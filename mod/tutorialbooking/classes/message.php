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

defined('MOODLE_INTERNAL') || die;

/**
 * Class for sending and viewing messages.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_message {
    /** VIEWALLMESSAGES = 1 All messages should be shown. */
    const VIEWALLMESSAGES = 1;

    /**
     * Generate the custom data that should be passed to a mod_tutorialbooking_email_form.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param stdClass $course The database record for the course that the tutorial is on.
     * @param int $tutorialid The id for a tutorial booking activity.
     * @param int $sessionid The id of the session to be updated.
     * @return mixed[] An array of information that shoulf be passed as the customdata for a mod_tutorialbooking_email_form.
     * @throws coding_exception
     */
    public static function generate_formdata($course, $tutorialid, $sessionid) {
        global $DB;

        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $sessionid), '*', MUST_EXIST);

        if ($session->tutorialid != $tutorialid) {
            throw new coding_exception('The session is not for the specified tutorial '
                    . 'mod_tutorialbooking_message::generate_formdata');
        }

        $formdata = array();
        $formdata['id'] = $sessionid;
        $formdata['tutorialid'] = $tutorialid;
        $formdata['courseid'] = $course->id;
        $formdata['title'] = $course->fullname;
        $formdata['stitle'] = clean_param($session->description, PARAM_TEXT);
        $formdata['subject'] = clean_param($session->description, PARAM_TEXT);

        return $formdata;
    }

    /**
     * Sends a message to a set of students. It stores a record of the message sent.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @global stdClass $USER The logged in user.
     * @param stdClass $tutorial The tutorial booking record.
     * @param mixed[] $message Should be the textbox parameters sent by a Moodle form.
     * @param int|stdClass[] $sendlist An array of user objects that the message should be sent to,
     *                              or the id of a session the message should be sent to.
     * @param string $subject The subject of the message that will be sent.
     * @return string A string showing if the live or test service was used to send the message.
     */
    public static function send_message($tutorial, array $message, $sendlist, $subject) {
        global $DB, $USER;

        $messagesent = false;

        if (!isset($message['text']) || !isset($message['format'])) {
            throw new coding_exception('Invalid message sent to mod_tutorialbooking_message::send_message $message');
        }

        if (!is_array($sendlist)) { // Session id sent, so retrive the users for the session.
            $sendlist = $DB->get_records('tutorialbooking_signups', array('sessionid' => $sendlist), null, 'userid');
        }

        $eventdata = new \core\message\message();
        $eventdata->component         = 'mod_tutorialbooking'; // Your component name.
        $eventdata->name              = 'notify'; // This is the message name from messages.php.
        $eventdata->userfrom          = $USER;
        $eventdata->subject           = $subject;
        $eventdata->fullmessage       = $message['text'];
        $eventdata->fullmessageformat = $message['format'];
        $eventdata->fullmessagehtml   = $message['text'];
        $eventdata->courseid          = $tutorial->course;
        $eventdata->smallmessage      = '';
        $eventdata->notification      = 1; // This is only set to 0 for personal messages between users.

        $userlist = array(); // Array of the ids of the users the message is sent to.

        $return = '';

        if ($liveservice = get_config('tutorialbooking', 'liveservice')) {
            // Get a list of users the message should be sent to.
            $return = get_string('liveservicemsg', 'tutorialbooking');
            foreach ($sendlist as $recipient) { // Send the messages.
                if (isset($recipient->userid)) { // A session id was passed.
                    $eventdata->userto = (int) $recipient->userid;
                } else { // It is a user object.
                    $eventdata->userto = (int) $recipient->id;
                }
                $userlist[] = $eventdata->userto;
                $messagesent = message_send($eventdata);
            }
        } else { // Only send to the admin user on non-live systems.
            $return = get_string('testservicemsg', 'tutorialbooking');
            $eventdata->userto = 2;
            $userlist[] = $eventdata->userto;
            $messagesent = message_send($eventdata);
        }

        if ($messagesent) { // Message was sent successfully so store it.
            $archivemessage = new stdClass();
            $archivemessage->tutorialbookingid = $tutorial->id;
            $archivemessage->sentby = $eventdata->userfrom->id;
            $archivemessage->sentto = serialize($userlist);
            $archivemessage->subject = $eventdata->subject;
            $archivemessage->message = $message['text'];
            $archivemessage->senttime = time();

            $DB->insert_record('tutorialbooking_messages', $archivemessage, false);
        }

        return $return;
    }

    /**
     * Retrives a page of messages that the logged in user can see.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @global stdClass $USER The logged in user's information.
     * @param int $tutorialid The id of the tutorial that the messages are from
     * @param int $courseid The id of the course the activity is in
     * @param int $viewallmessages 0 show own messages only, 1 display messages from all users (if user has correct capability)
     * @param int $page The page of messages to display, 0 is the first page.
     * @param int $maxrecords The number of records per page.
     * @return stdClass object containing information about the messages to be displayed on the page.
     */
    public static function get_messages($tutorialid, $courseid, $viewallmessages, $page, $maxrecords) {
        global $DB, $USER;

        $return = new stdClass();
        $return->tutorialid = $tutorialid;
        $return->courseid = $courseid;
        $return->viewallmessages = $viewallmessages;
        $return->page = $page;
        $return->maxrecords = $maxrecords;

        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorialid, $courseid);
        $context = context_module::instance($cm->id);

        $return->can_view_all = has_capability('mod/tutorialbooking:viewallmessages', $context);

        // The user has requested and has permission to view all messages sent by the activity.
        if ($viewallmessages == self::VIEWALLMESSAGES && $return->can_view_all) {
            $where = array('tutorialbookingid' => $tutorialid);
        } else { // Just display messages the user sent via this activity.
            $where = array('tutorialbookingid' => $tutorialid, 'sentby' => $USER->id);
        }

        // Retrieve the message list.
        $messages = $DB->get_recordset('tutorialbooking_messages', $where, 'senttime DESC',
                'id,sentby,subject,sentto,senttime,message', ($page * $maxrecords), $maxrecords);
        $return->messages = new \mod_tutorialbooking\renderable\messages($messages);
        $messages->close();
        $return->totalmessages = $DB->count_records('tutorialbooking_messages', $where);

        return $return;
    }
}
