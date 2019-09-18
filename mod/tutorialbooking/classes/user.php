<?php
// This file is part of the Tutorial Booking activity.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use mod_tutorialbooking\event\signup_added;
use mod_tutorialbooking\event\signup_removed;
use mod_tutorialbooking\event\signup_teacher_added;
use mod_tutorialbooking\event\signup_teacher_removed;

defined('MOODLE_INTERNAL') || die;

/**
 * Code for manipulating users.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_user {
    /**
     * Adds a user to a signup slot.
     *
     * @global moodle_database $DB The moodle database connection object.
     * @global moodle_page $PAGE The current page.
     * @param int $userid Id of the user to be added.
     * @param stdclass $session Details of the slot the user should be signed up to.
     * @param stdClass $tutorial Details of the tutorial booking that the user is being signed upto.
     * @param context_module $tutorialbookingcontext The context of the tutorial booking.
     * @param completion_info $completion Completion object for the course should be passed if completion is enabled.
     * @param stdClass $cm The course_module information for the course.
     * @param moodle_url $errorurl The URL the function should return a user to if there is an error.
     * @param bool $extratests If true several extra tests should be carried out to ensure the user is not doing something wrong.
     * @return void
     */
    public static function adduser($userid, $session, $tutorial, context_module $tutorialbookingcontext,
            completion_info $completion, $cm, moodle_url $errorurl, $extratests = false) {
        global $DB;

        if ($extratests) {
            $session = $DB->get_record('tutorialbooking_sessions', array('id' => $session->id));
            $attendees = $DB->count_records('tutorialbooking_signups', array('sessionid' => $session->id));
            if ($tutorial->locked) { // The session has been locked.
                print_error('lockederror', 'tutorialbooking', $errorurl->out(), '', false);
            } else if (!has_capability('mod/tutorialbooking:submit', $tutorialbookingcontext, $userid)) {
                print_error('unauthorised', 'tutorialbooking', $errorurl->out(), '', false);
            } else if ($attendees >= $session->spaces) {
                print_error('sessionfull', 'tutorialbooking', $errorurl->out(), '', false);
            }
        }

        // Verify that the user is not already enrolled.
        $alreadysignedup = $DB->record_exists('tutorialbooking_signups', array('userid' => $userid, 'tutorialid' => $tutorial->id));
        if (!$alreadysignedup) {
            $data = new StdClass();
            $data->userid = $userid;
            $data->courseid = $tutorial->course;
            $data->tutorialid = $tutorial->id;
            $data->sessionid = $session->id; // Must be passed in.
            $data->signupdate = time();
            $data->waiting = 0; // Default not on the waiting list.
            $data->blocked = 0; // Default not blocked.
            $data->blockerid = null;
            $data->blockdate = null;

            // Add them the the session.
            $data->id = $DB->insert_record('tutorialbooking_signups', $data);

            if ($completion->is_enabled($cm) && !empty($tutorial->completionsignedup)) {
                // Update their completion.
                $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
            }
        } else {
            print_error('useralreadysignedup', 'tutorialbooking', $errorurl->out(),
                            array('id' => $userid));
        }

        // Fire the appropriate event.
        $eventdata = array(
            'context' => $tutorialbookingcontext,
            'objectid' => $data->id,
            'other' => array(
                'tutorialid' => $tutorial->id,
                'tutorialname' => $tutorial->name,
                'sessionid' => $session->id,
            ),
        );
        if ($extratests) {
            $event = signup_added::create($eventdata);
        } else {
            $eventdata['relateduserid'] = $userid;
            $event = signup_teacher_added::create($eventdata);
        }
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('tutorialbooking', $tutorial);
        $event->add_record_snapshot('tutorialbooking_sessions', $session);
        $event->add_record_snapshot('tutorialbooking_signups', $data);
        $event->trigger();
    }

    /**
     * Processes the output of a user add form by adding the users into a tutorial booking slot.
     *
     * @global moodle_database $DB The moodle database connection object.
     * @param int $courseid The id of the course the tutorial is on.
     * @param stdClass $tutorial The database record of the tutorial that the user is being signed up to.
     * @param context_module $tutorialbookingcontext The conext of the tutorial booking activity.
     * @param completion_info $completion The completion object for the course.
     * @param stdClass $cm The course module information.
     * @param int $sessionid The id of the session the form is for.
     * @param int[] $toadd An array of ids for users who should be added.
     * @return void
     */
    public static function addusers_from_form($courseid, $tutorial, context_module $tutorialbookingcontext,
            completion_info $completion, $cm, $sessionid, $toadd) {
        global $DB;

        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $sessionid), '*', MUST_EXIST);

        // URL to send users to if there is an error.
        $errorurl = new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php',
                array('tutorialid' => $tutorial->id, 'courseid' => $courseid));

        if (!has_capability('mod/tutorialbooking:oversubscribe', $tutorialbookingcontext)) {
            // We need to see if their are enough space left for the users they have selected.
            $sessionstats = mod_tutorialbooking_session::getsessionstats($session->id);

            if ($sessionstats->places < ($sessionstats->signedup + count($toadd))) {
                // The session will be oversubscribed so display an error to the user.
                print_error('oversubscribed', 'tutorialbooking', $errorurl,
                        array('freeslots' => ($sessionstats->places - $sessionstats->signedup),
                            'numbertoadd' => count($toadd),
                            'timeslotname' => strip_tags($session->description)));
            }
        }

        // We now know there are either enough free slots, or that the user can oversubscribe so add the users.
        foreach ($toadd as $userid) {
            self::adduser($userid, $session, $tutorial, $tutorialbookingcontext, $completion, $cm, $errorurl, false);
        }
    }

    /**
     * Generates the data needed by the mod_tutorialbooking_confirmremoval_form.
     *
     * @global moodle_database $DB The moodle database connection object.
     * @param int $tutorialid The id of the tutorial.
     * @param int $courseid The id of the course.
     * @param int $userid The id of the user to be removed.
     * @return mixed[] The data for the mod_tutorialbooking_confirmremoval_form customdata fields.
     */
    public static function generate_removeuser_formdata($tutorialid, $courseid, $userid) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $userid), 'id, firstname, lastname, username');

        // Get information about the timeslot they are in.
        $signup = $DB->get_record('tutorialbooking_signups', array('tutorialid' => $tutorialid, 'userid' => $userid),
                'id, sessionid');
        $timeslot = $DB->get_record('tutorialbooking_sessions', array('id' => $signup->sessionid), 'id, description');

        // Create custom data for the form.
        $formdata = array();
        $formdata['userid'] = $userid;
        $formdata['username'] = $user->firstname.' '.$user->lastname.' ('.$user->username.')';
        $formdata['timeslotname'] = $timeslot->description;
        $formdata['timeslotid'] = $timeslot->id;
        $formdata['tutorialid'] = $tutorialid;
        $formdata['courseid'] = $courseid;

        return $formdata;
    }

    /**
     * Removes a user from a timeslot.
     *
     * @global moodle_database $DB The moodle database connection object.
     * @param int $userid The id of the user to be removed.
     * @param stdClass $tutorial The database record for the tutorial.
     * @param completion_info $completion The completion object for the course.
     * @param stdClass $cm The course module information.
     * @param bool $messageuser If true the user being removed is sent a message. This should always be
     *      done if the user is not removing themselves.
     * @param array $msg The message to be sent to the user.
     * @return stdClass
     */
    public static function remove_user($userid, $tutorial, completion_info $completion, $cm, $messageuser = false, $msg = null) {
        global $DB;

        $timeslotid = null;
        if ($messageuser) {
            $timeslotid = self::message_removed_user($userid, $tutorial, $msg);
        } else if (!has_capability('mod/tutorialbooking:submit', context_module::instance($cm->id))) {
            // If no message is being sent then it must be the user themselves doing the removal.
            // In this case they do not have the capability required.
            print_error('unauthorised', 'tutorialbooking');
        } else if ($tutorial->locked) {
            // The tutorial is locked, do not allow the user to remove themaselves.
            print_error('lockederror', 'tutorialbooking');
        }

        $signup = $DB->get_record('tutorialbooking_signups', array('tutorialid' => $tutorial->id, 'userid' => $userid));
        if ($signup === false) {
            // No signup found.
            $url = new moodle_url('/mod/tutorialbooking/view.php', array('id' => $cm->id));
            print_error('nosignup', 'tutorialbooking', $url);
        }
        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $signup->sessionid));

        // Remove the user from the timeslots they are in in this tutorialbooking instance (they should only be in a single one).
        $DB->delete_records('tutorialbooking_signups', array('userid' => $userid, 'tutorialid' => $tutorial->id));

        // Update the user's completion status, as it may change with their removal.
        if ($completion->is_enabled($cm) && !empty($tutorial->completionsignedup)) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $userid);
        }

        // Fire the appropriate event.
        $eventdata = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $signup->id,
            'other' => array(
                'tutorialid' => $tutorial->id,
                'tutorialname' => $tutorial->name,
                'sessionid' => $session->id,
            ),
        );
        if ($messageuser) {
            $eventdata['relateduserid'] = $userid;
            $event = signup_teacher_removed::create($eventdata);
        } else {
            $event = signup_removed::create($eventdata);
        }
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('tutorialbooking', $tutorial);
        $event->add_record_snapshot('tutorialbooking_sessions', $session);
        $event->add_record_snapshot('tutorialbooking_signups', $signup);
        $event->trigger();

        return (object)array('user' => $userid, 'timeslot' => $timeslotid);
    }

    /**
     * Sends a message to a user who is being removed from a tutorial slot.
     * It should always be used by self::remove_user() when the removal is
     * triggered by someone other than the user beign removed.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $userid The id of the user to be removed.
     * @param stdClass $tutorial The database record for the tutorial the user s on.
     * @param array $msg The message to be sent to the user.
     * @return int The id of the timeslot the user is on.
     */
    protected static function message_removed_user($userid, $tutorial, $msg) {
        global $DB;

        // Get information about the timeslot they are in.
        $signup = $DB->get_record('tutorialbooking_signups', array('tutorialid' => $tutorial->id, 'userid' => $userid),
                'id, sessionid', MUST_EXIST);
        $timeslot = $DB->get_record('tutorialbooking_sessions', array('id' => $signup->sessionid), 'id, description', MUST_EXIST);

        // Send a message to the user.
        $subject = get_string('removalmessagesubject', 'tutorialbooking', array('timeslot' => strip_tags($timeslot->description)));
        $sendlist = $DB->get_records('user', array('id' => $userid));
        mod_tutorialbooking_message::send_message($tutorial, $msg, $sendlist, $subject);

        return $timeslot->id;
    }

    /**
     * Create a textual representation of users based on an array of user ids.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int[] $userids An array of user ids.
     * @return array
     */
    public static function displayusernames(array $userids) {
        global $DB;
        // Build up a where statment and the parameters for it.
        $where = array();
        $params = array();
        foreach ($userids as $key => $id) {
            $params['user'.$key] = $id;
            $where[] = 'id = :user'.$key;
        }
        $where = implode(' OR ', $where);

        // Do the query.
        $fields = 'id,username,firstname,lastname,firstnamephonetic,lastnamephonetic,middlename,alternatename';
        $users = $DB->get_recordset_select('user', $where, $params, 'lastname,firstname', $fields);

        // Format the user information.
        $userlist = array();
        foreach ($users as $user) {
            $userdetails = array('name' => fullname($user), 'username' => $user->username);
            $userlist[] = get_string('userdisplay', 'mod_tutorialbooking', $userdetails);
        }
        $users->close();
        return $userlist;
    }
}
