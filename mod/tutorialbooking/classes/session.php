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

use mod_tutorialbooking\event\session_deleted;
use mod_tutorialbooking\event\session_added;
use mod_tutorialbooking\event\session_updated;

defined('MOODLE_INTERNAL') || die;

/**
 * Code for changing a session.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_session {
    /** POSITION_FIRST = 1 This is the first of more than one session. */
    const POSITION_FIRST = 1;

    /** POSITION_LAST = 2 This is the last of more than one session. */
    const POSITION_LAST = 2;

    /** POSITION_NEXT = 3 This is neither the first nor last session. */
    const POSITION_NEXT = 3;

    /** POSITION_ONLY = 4 This is the only session. */
    const POSITION_ONLY = 4;

    /** SESSION_ADD = 'addsession' The session is new and should be inserted into the database. */
    const SESSION_ADD = 'addsession';

    /** SESSION_UPDATE = 'updatesession' The session already exists and should be updated. */
    const SESSION_UPDATE = 'updatesession';

    /**
     * Changes the sequencing of the sessions based on the tutorial sessions form dropdown.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $oldposition The current sequence position of the item.
     * @param int $newposition The required sequence position of the item.
     * @param int $tutorialid The id of the tutorial.
     * @return int The resultant position of the item.
     */
    public static function computesequence($oldposition, $newposition, $tutorialid) {
        global $DB;
        $position = 0; // Return the proper sequence place.

        // This is to tackle the bottom/last item otherwise we get spaces in our sequence
        // but we only need to do this if newposition is higher than oldposition.
        if ($newposition > $oldposition) {
            $maxsequence = self::get_max_sequence_value($tutorialid);
            if ($newposition > $maxsequence) {
                $newposition = $maxsequence;
            }
        }

        // If both have a value.
        if ($oldposition && $newposition) {
            $sql = 'UPDATE {tutorialbooking_sessions} SET sequence = sequence + ? '
                    . 'WHERE sequence >= ? AND sequence <= ? AND tutorialid = ?';
            if ($oldposition > $newposition) { // If they are  not the same.
                // If moving from high number to low
                // shift everything > low number < higher number + 1.
                $DB->execute($sql, array(1, $newposition, $oldposition, $tutorialid));
                $position = $newposition;
            } else if ($oldposition < $newposition) {
                // If moving from low to high
                // shift everything > low number < high number - 1.
                $DB->execute($sql, array(-1, $oldposition, $newposition, $tutorialid));
                $position = $newposition;
            } else { // Nothing's changed - the values are the same.
                $position = $oldposition;
            }
        } else {
            if ($newposition) {
                $position = $newposition; // Assume newposition has a value.
            } else {
                $position = $oldposition;
            }
        }

        return $position;
    }

    /**
     * Deletes a session from a tutorial and then removes the gap in the sequence.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $tutorialid The id of the tutorial the session is in.
     * @param int $sessionid The id of the session.
     * @return void
     */
    public static function delete_session($tutorialid, $sessionid) {
        global $DB;

        // Get the sequence for the session we are about to delete, as we are required to add snapshots to the event.
        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $sessionid, 'tutorialid' => $tutorialid), '*', MUST_EXIST);
        $deletedsignups = $DB->get_records('tutorialbooking_signups', array('sessionid' => $sessionid));

        // Just delete the session related stuff.
        $DB->delete_records('tutorialbooking_sessions', array('id' => $sessionid));

        // Delete any signups in the session.
        $DB->delete_records('tutorialbooking_signups', array('sessionid' => $sessionid));

        // Update the sequence of all records that had a sequence higher than the deleted record.
        $params = array();
        $params['tutorialid'] = $tutorialid;
        $params['sequenceid'] = $session->sequence;
        $sql = 'UPDATE {tutorialbooking_sessions} SET sequence = sequence - 1 '
                . 'WHERE tutorialid = :tutorialid AND sequence > :sequenceid';
        $DB->execute($sql, $params);

        // Create an event for the deleted session.
        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorialid);
        $eventdata = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $session->id,
            'other' => array(
                'tutorialid' => $tutorialid
            ),
        );
        $event = session_deleted::create($eventdata);
        $event->add_record_snapshot('tutorialbooking_sessions', $session);
        foreach ($deletedsignups as $deleted) {
            $event->add_record_snapshot('tutorialbooking_signups', $deleted);
        }
        $event->trigger();
    }

    /**
     * Generate the data that is passed to a mod_tutorialbooking_session_form to allow it to display correctly.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @global stdClass $USER The Object storing data about the currently logged in user.
     * @param int $courseid The id of the course.
     * @param stdClass $tutorial The database record of the tutorial the form is for.
     * @param int $sessionid The is of a tutorial booking session.
     * @return mixed[] An array of data to be padded to the form.
     */
    public static function generate_editsession_formdata($courseid, $tutorial, $sessionid) {
        global $DB, $USER;

        $formdata = array();
        $formdata['id'] = $sessionid;
        if ($formdata['id']) {
            $record = $DB->get_record('tutorialbooking_sessions', array('id' => $formdata['id']), '*', MUST_EXIST);
            $formdata = (array) $record;
            $formdata['title'] = $formdata['description'];
            $formdata['summary'] = array('text' => $record->summary, 'format' => $record->summaryformat);
        } else {
            // Set some defaults.
            $formdata['tutorialid'] = $tutorial->id;
            $formdata['visible'] = 0; // Default off.
            $formdata['usercreated'] = $USER->id;
            $formdata['title'] = $tutorial->name;

            $formdata['spaces'] = get_config('tutorialbooking', 'defaultnumbers');

            // Work out where this new record will sit in the sequence.
            $formdata['sequence'] = (self::get_max_sequence_value($tutorial->id)) + 1; // Defauly last on the list.
        }
        $formdata['courseid'] = $courseid;

        // Sequencing.
        $formdata['positions'] = array();
        $allsessions = mod_tutorialbooking_tutorial::gettutorialsessions($tutorial->id, true);
        $position = 1; // Top of the page.
        $formdata['positions'][$position] = get_string('positionfirst', 'tutorialbooking');
        foreach ($allsessions as $session) {
            if ($session->id != $formdata['id']) {
                $position = $session->sequence + 1;
                $formdata['positions'][$position] = get_string('after', 'mod_tutorialbooking',
                        array('session' => substr($session->description, 0, 30)));
            }
        }
        if ($position > 1) { // This overwrites the last option above but that's OK because that is the bottom of the page.
            if (($formdata['id']) == $position) { // This is needed to ensure that the last slot can create a record below itself.
                unset($formdata['positions'][$position]);
                $position++;
            }
            $formdata['positions'][$position] = get_string('positionlast', 'tutorialbooking');
        }

        return $formdata;
    }

    /**
     * Get the highest sequence value for the tutorial booking activity.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $tutorialid The id of a tutorial booking activity.
     * @return int The highest sequence value for the tutorial booking.
     */
    protected static function get_max_sequence_value($tutorialid) {
        global $DB;
        $params = array();
        $params['tutorialid'] = $tutorialid;
        $sql = 'SELECT MAX(sequence) AS seq FROM {tutorialbooking_sessions} WHERE tutorialid = :tutorialid';
        return (int) $DB->get_field_sql($sql, $params, MUST_EXIST);
    }

    /**
     * Get the session's stats from the database. Used by the confirm deletion page.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $sessionid The tutorial id.
     * @return stdClass Stats as calculated by the database.
     */
    public static function getsessionstats($sessionid) {
        global $DB;

        $sql = 'SELECT ses.spaces AS places, COUNT(sup.userid) AS signedup '
                . 'FROM {tutorialbooking_sessions} ses '
                . 'LEFT JOIN {tutorialbooking_signups} sup ON ses.id = sup.sessionid '
                . 'WHERE ses.id = ? GROUP BY ses.spaces';

        return $DB->get_record_sql($sql, array($sessionid)); // Return the only record.
    }

    /**
     * Gets the records for each tutorial booking slot.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param array $slots An array of slot ids to get records for.
     * @return array
     */
    public static function get_slot_records(array $slots) {
        global $DB;
        list($insql, $params) = $DB->get_in_or_equal($slots, SQL_PARAMS_NAMED, 'sessionid');
        return $DB->get_records_select('tutorialbooking_sessions', "id $insql", $params);
    }

    /**
     * Moves a session from it's current position to a new position,
     * and resequence any records between the two positions.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $tutorialid The id of a tutorial booking activity.
     * @param int $currentposition The current position of a session in the tutorial booking.
     * @param int $targetposition The position the session should be moved to.
     * @return boolean On success returns true, on a failure it returns false.
     */
    public static function move_sequence($tutorialid, $currentposition, $targetposition) {
        global $DB;

        // Nothing to move.
        if ($currentposition == $targetposition) {
            return false;
        }

        // The minimum sequence value is 1.
        if ($currentposition < 1 || $targetposition < 1) {
            return false;
        }

        // We cannot move a slot to a sequence greater than the highest one.
        $maxsequence = self::get_max_sequence_value($tutorialid);
        if ($currentposition > $maxsequence || $targetposition > $maxsequence) {
            return false;
        }

        // Get the id if the session in the current location.
        $currentid = $DB->get_field('tutorialbooking_sessions', 'id',
                array('sequence' => $currentposition, 'tutorialid' => $tutorialid));

        // Move the other records affected.
        if ($currentposition > $targetposition) {
            $action = 'sequence = sequence + 1';
            $where = 'sequence >= :target AND sequence < :current';
        } else if ($currentposition < $targetposition) {
            $action = 'sequence = sequence - 1';
            $where = 'sequence > :current AND sequence <= :target';
        }
        $params = array('tutorial' => $tutorialid, 'current' => $currentposition, 'target' => $targetposition);
        $sql = "UPDATE {tutorialbooking_sessions} SET $action WHERE $where AND tutorialid = :tutorial";
        $DB->execute($sql, $params);

        // Move the session we want to.
        $DB->set_field('tutorialbooking_sessions', 'sequence', $targetposition, array('id' => $currentid));

        return true;
    }

    /**
     * Moves the tutorial down in the sequence.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $tutorialid The id of the tutorial that is being editied.
     * @param int $currentposition The sequence number of the slot to be moved.
     * @return bool Result of update to table, will only return true.
     */
    public static function move_sequence_down($tutorialid, $currentposition) {
        global $DB;

        if ($currentposition < self::get_max_sequence_value($tutorialid)) { // The sequence is not the highest value.
            $newposition = $currentposition + 1;
            $params = array(
                'tutorialid' => $tutorialid,
                'current1' => $currentposition,
                'current2' => $currentposition,
                'current3' => $currentposition,
                'new1' => $newposition,
                'new2' => $newposition,
                );
            $sql = "UPDATE {tutorialbooking_sessions} SET sequence = CASE sequence WHEN ".$DB->sql_cast_char2int(':current1').
                    " THEN ".$DB->sql_cast_char2int(':new1').
                    " ELSE ".$DB->sql_cast_char2int(':current2').
                    " END WHERE (sequence = :new2 OR sequence = :current3) AND tutorialid = :tutorialid";

            return $DB->execute($sql, $params);
        }
        return false;
    }

    /**
     * Moves the tutorial up in the sequence.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $tutorialid The id of the tutorial that is being editied.
     * @param int $currentposition The sequence number of the slot to be moved.
     * @return bool Result of update to table.
     */
    public static function move_sequence_up($tutorialid, $currentposition) {
        global $DB;

        if ($currentposition > 1) {
            $newposition = $currentposition - 1;
            $params = array(
                'tutorialid' => $tutorialid,
                'current1' => $currentposition,
                'current2' => $currentposition,
                'current3' => $currentposition,
                'new1' => $newposition,
                'new2' => $newposition,
                );

            $sql = "UPDATE {tutorialbooking_sessions} SET sequence = CASE sequence WHEN ".$DB->sql_cast_char2int(':current1').
                    " THEN ".$DB->sql_cast_char2int(':new1').
                    " ELSE ".$DB->sql_cast_char2int(':current2').
                    " END WHERE (sequence = :new2 OR sequence = :current3) AND tutorialid = :tutorialid";

            return $DB->execute($sql, $params);
        }
        return false; // Only gets here if the update does not happen.
    }

    /**
     * Turns the tutorial visibility on or off.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $id The id of the session (timeslot).
     * @param bool $visible A flag for the editing status.
     * @return bool true If the update is a success.
     */
    public static function togglevisiblity($id, $visible) {
        global $DB;

        $data = new StdClass();
        $data->id = $id;
        if ($visible) {
            $data->visible = 1;
        } else {
            $data->visible = 0;
        }
        return $DB->update_record('tutorialbooking_sessions', $data);
    }

    /**
     * Make changes to a tutorial booking slot, or copy an existing slot.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @global stdClass $USER The logged in user.
     * @global moodle_page $PAGE The current page in Moodle.
     * @param int $tutorialid The id of the tutorial that should be updated.
     * @param mixed[] $formdata Custom data to be passed to the mod_tutorialbooking_session_form.
     * @return \stdClass A standard class with two properties:
     *                      id - the id of the session that was affected.
     *                      action - the type of action that was performed.
     * @throws mod_tutorialbooking\exception\session_exception is thrown if the user tries to reduce
     *          the number of spaces to less than the number of signup, but do not have the capability
     *          to cause over subscription.
     */
    public static function update_session($tutorialid, $formdata) {
        global $DB, $USER, $PAGE;

        $return = new stdClass(); // Stores information to be returned by the function.

        $wmform = new mod_tutorialbooking_session_form(null, $formdata); // Use this to get the submitted data.
        $data = $wmform->get_data();
        $data->spaces = self::validate_spaces($data->spaces);

        // Make sure we keep sequences.
        $maxsequence = self::get_max_sequence_value($tutorialid);
        if ($data->newposition > ($maxsequence + 1)) {
            $data->newposition = $maxsequence + 1;
        }

        // Special case for saving as a new slot.
        if (isset($data->saveasnew)) {
            $data->id = 0;
            $data->sequence = $maxsequence + 1; // Set to bottom of list by default.
        }

        // Common code for save.
        $data->sequence = self::computesequence($data->sequence, $data->newposition, $tutorialid);

        // Force some descriptive text, if the user has not entered any.
        if (trim($data->description) === '') {
            $data->description = get_string('defaultdescription', 'mod_tutorialbooking', $data->sequence);
        }
        // Plain text.
        $data->descformat = FORMAT_PLAIN;

        $data->summaryformat = $data->summary['format'];
        $data->summary = $data->summary['text'];

        if (!$data->id) { // This is for new records.
            unset($data->id);
            $data->usercreated = $USER->id;
            $data->timecreated = time();
            $data->visible = 1;
            $return->id = $DB->insert_record('tutorialbooking_sessions', $data);
            $return->action = self::SESSION_ADD;
        } else {
            // Check that we are not reducing the places to less than the signups.
            $stats = self::getsessionstats($data->id);
            if ($stats->signedup > $data->spaces && !has_capability('mod/tutorialbooking:oversubscribe', $PAGE->context)) {
                $stats->spaces = $data->spaces;
                throw new mod_tutorialbooking\exception\session_exception(get_string('editspaceserror', 'tutorialbooking', $stats));
            }

            $data->timemodified = time();
            $originalsession = $DB->get_record('tutorialbooking_sessions', array('id' => $data->id));
            $DB->update_record('tutorialbooking_sessions', $data);
            $return->id = $data->id;
            $return->action = self::SESSION_UPDATE;
        }

        // Fire the appropriate event.
        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorialid);
        $eventdata = array(
            'context' => context_module::instance($cm->id),
            'objectid' => $return->id,
            'other' => array(
                'tutorialid' => $tutorialid,
            ),
        );
        if ($return->action == self::SESSION_ADD) {
            $event = session_added::create($eventdata);
        } else {
            $event = session_updated::create($eventdata);
            $event->add_record_snapshot('tutorialbooking_sessions', $originalsession);
        }
        $event->add_record_snapshot('course_modules', $cm);
        $event->trigger();

        return $return;
    }

    /**
     * Ensures the number of space is not going to generate an error.
     *
     * @param int $spaces
     * @return int
     */
    protected static function validate_spaces($spaces) {
        if ($spaces < 1) {
            return 1;
        } else if ($spaces > 30000) {
            return 30000;
        } else {
            return $spaces;
        }
    }
}
