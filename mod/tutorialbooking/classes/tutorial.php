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
 * Code for changing a tutorial.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_tutorial {
    /** PRIVACY_SHOWSIGNUPS = 1 Students can see who has signed up to tutorial slots. */
    const PRIVACY_SHOWSIGNUPS = 1;

    /** PRIVACY_SHOWOWN = 2 students cannot see the names of other people who have signed up to tutorial slots. */
    const PRIVACY_SHOWOWN = 2;

    /**
     * Get the tutorial's stats from the database. Used by the confirm deletion page.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $tutorialid The tutorial id.
     * @return stdClass A $DB row object.
     */
    public static function getstatsfortutorial($tutorialid) {
        global $DB;

        $sql = 'SELECT  places.spaces AS places, signedup.count AS signedup '
                . 'FROM (SELECT SUM(ses.spaces) AS spaces FROM {tutorialbooking_sessions} ses WHERE ses.tutorialid = ?) places, '
                . '(SELECT COUNT(sup.tutorialid) AS count FROM {tutorialbooking_signups} sup WHERE sup.tutorialid = ?) signedup';

        return $DB->get_record_sql($sql, array($tutorialid, $tutorialid)); // Return the only record.
    }

    /**
     * Change the lock status of a tutorial booking.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $id The id of the tutorial that is being modified.
     * @param bool $lock The new lock status of the tutorial booking activity.
     * @return true If the lock status is changed.
     */
    public static function togglelock($id, $lock) {
        global $DB;

        $data = new stdClass();
        $data->id = $id;
        if ($lock) {
            $data->locked = 1;
        } else {
            $data->locked = 0;
        }

        return $DB->update_record('tutorialbooking', $data);
    }

    /**
     * Returns all the sessions in a tutorial.
     *
     * @global moodle_databae $DB The Moodle database connection object.
     * @param int $tutorialid The id of the tutorial.
     * @return stdClass[] $DB row objects
     */
    public static function gettutorialsessions($tutorialid) {
        global $DB;
        $sessions = array();
        $sessions = $DB->get_records('tutorialbooking_sessions', array('tutorialid' => $tutorialid), 'sequence');
        return $sessions;
    }

    /**
     * Return list of user's names for each session in a tutorial.
     *
     * @global moodle_databae $DB The Moodle database connection object.
     * @param int $tutorialid The tutorial's id
     * @return array An array of aarrys of user details in keys blocked, waiting and signedup
     */
    public static function gettutorialsignups($tutorialid) {
        global $DB;
        $signedup = array(); // Returns a structured array of signups.

        $sql = "SELECT u.id AS uid, u.username, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, "
                . "u.middlename, u.alternatename, t.* "
                . "FROM {user} u, {tutorialbooking_signups} t "
                . "WHERE t.userid = u.id AND t.tutorialid = ? "
                . "ORDER BY u.lastname, u.firstname";

        $signups = $DB->get_records_sql($sql, array($tutorialid));

        foreach ($signups as $signup) {
            if (!isset($signedup[$signup->sessionid])) { // Stop warnings.
                $signedup[$signup->sessionid] = array('waiting' => array(), 'signedup' => array(), 'total' => 0);
            }
            $signup->fname = fullname($signup);
            $record = (array) $signup; // Turn the object into an array - not sure why but there you go...
            if ($signup->waiting) {
                $signedup[$signup->sessionid]['waiting'][] = $record;
            } else {
                $signedup[$signup->sessionid]['signedup'][] = $record;
                $signedup[$signup->sessionid]['total'] += 1;
            }
        }

        return $signedup;
    }

    /**
     * Get the id of the slot that a user is signed up to.
     *
     * @global moodle_database $DB
     * @global type $USER
     * @param int $tutorialid The id of the tutorial booking to be searched
     * @param int $userid (optional) The id of the user that the sign up should
     *                     be found for, if not passed the current user is searched for.
     * @return bool|int False if no sign up found, or the id of the slot
     */
    public static function get_signup($tutorialid, $userid = null) {
        global $DB, $USER;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        return $DB->get_field('tutorialbooking_signups', 'sessionid', array('tutorialid' => $tutorialid, 'userid' => $userid));
    }

    /**
     * Return the total stats for this tutorial - internal to locallib.
     *
     * @param array $sessions An array of $DB row objects, from self::gettutorialsessions.
     * @param array $signups An array of array of blocked, signedup and waitinglist users from self::gettutorialsignups.
     * @return int[][] Array containing the totals.
     */
    public static function gettutorialstats($sessions, $signups) {
        $stats = array(
            'places' => 0,
            'waiting' => 0,
            'signedup' => 0,
        );

        foreach ($sessions as $session) {
            $stats['places'] += $session->spaces; // Total spaces.
            if (isset($signups[$session->id])) {
                $stats['waiting'] += count($signups[$session->id]['waiting']); // Total on waiting list.
                $stats['signedup'] += count($signups[$session->id]['signedup']); // Total signed up ~ no waiting list.
            }
        }

        return $stats;
    }

    /**
     * Creates, updates and deletes events for a tutorial booking when it is updated, or created.
     *
     * @param stdClass $tutorialbooking form data.
     * @param mod_tutorialbooking_mod_form $mform
     */
    public static function update_events(stdClass $data) {
        $completionexpected = (!empty($data->completionexpected)) ? $data->completionexpected : null;
        \core_completion\api::update_completion_date_event(
                $data->coursemodule,
                'tutorialbooking',
                $data->id,
                $completionexpected
        );
    }

    /**
     * Deletes events for a tutorial booking activity when it is deleted.
     *
     * @param int $id The id of the tutorial booking activity.
     */
    public static function delete_events($id) {
        $cm = get_coursemodule_from_instance('tutorialbooking', $id, 0, false, MUST_EXIST);
        \core_completion\api::update_completion_date_event($cm->id, 'tutorialbooking', $id, null);
    }
}
