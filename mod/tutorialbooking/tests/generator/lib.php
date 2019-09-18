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

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tutorialbooking data generator.
 *
 * @package    mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_generator extends testing_module_generator {
    /** @var int $slotcount Count of the number of sessions created by the data generator. */
    protected $slotcount = 0;

    /**
     * Create a Tutorial booking session slot.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @global stdClass $USER The logged in Moodle user.
     * @param stdClass $tutorial A database record for the tutorial the slot will be added to.
     * @param stdClass|array $record Information to be part of the tutorial booking slot to be created.
     * @return stdClass The database record for the new slot.
     * @throws coding_exception
     */
    public function add_slot($tutorial, $record = null) {
        global $DB, $USER;

        if (!isset($tutorial->id)) {
            throw new coding_exception('A valid tutorialbooking record must be passed to '
                    . 'mod_tutorialbooking_generator::add_slot $tutorial');
        }

        $record = (object)(array)$record;

        $record->tutorialid = $tutorial->id;

        if (!isset($record->visible)) {
            $record->visible = true;
        }

        if (!isset($record->sequence)) {
            // We are only letting this be set outside of here so that the functions that reorder sequence can be tested.
            $record->sequence = $DB->count_records('tutorialbooking_sessions', array('tutorialid' => $tutorial->id)) + 1;
        }

        if (!isset($record->description)) {
            $record->description = html_writer::tag('p', 'Timeslot '.$this->slotcount++);
            $record->descformat = FORMAT_HTML;
        }

        if (!isset($record->descformat)) {
            $record->descformat = FORMAT_HTML;
        }

        if (!isset($record->summary)) {
            $record->summary = html_writer::tag('p', 'My wonderful descriptive text');
            $record->summaryformat = FORMAT_HTML;
        }

        if (!isset($record->summaryformat)) {
            $record->summaryformat = FORMAT_HTML;
        }

        if (!isset($record->location)) {
            $record->location = false; // Completion off by default.
        }

        if (!isset($record->spaces)) {
            $record->spaces = 30;
        }

        if (!isset($record->usercreated)) {
            $record->usercreated = $USER->id; // Assume the current user added it.
        }

        if (!isset($record->timecreated)) {
            $record->timecreated = time(); // The current time is used by default.
        }

        if (!isset($record->timemodified)) {
            $record->timemodified = time(); // The current time is used by default.
        }

        $id = $DB->insert_record('tutorialbooking_sessions', $record);
        return $DB->get_record('tutorialbooking_sessions', array('id' => $id));
    }

    /**
     * Creates an instance of the module for testing purposes.
     *
     * Module type will be taken from the class name. Each module type may overwrite
     * this function to add other default values used by it.
     *
     * @param array|stdClass $record Data for module being generated. Requires 'course' key
     *     (an id or the full object). Also can have any fields from add module form.
     * @param null|array $options General options for course module. Since 2.6 it is
     *     possible to omit this argument by merging options into $record
     * @return stdClass Record from module-defined table with additional field
     *     cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        if (!isset($record->locked)) {
            $record->locked = false; // Leave unlocked by default.
        }

        if (!isset($record->completionsignedup)) {
            $record->completionsignedup = false; // Completion off by default.
        }

        if (!isset($record->privacy)) {
            $record->privacy = false; // All signups can be seen by default.
        }

        return parent::create_instance($record, (array)$options);
    }

    /**
     * To be called from data reset code only,
     * do not use in tests.
     *
     * @return void
     */
    public function reset() {
        $this->slotcount = 0;
        parent::reset();
    }

    /**
     * Signs a user upto a tutorial booking.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param stdClass $tutorial A database record for the tutorial the slot that the user will be added to.
     * @param stdClass $session A database record for the slot that the user is being signed up to.
     * @param stdClass $user A database record for the Moodle user who will be signed up.
     * @param int $time A UNIX timestamp that the signup happened on (optional).
     * @return stdClass The database record for the signup created.
     * @throws coding_exception
     */
    public function signup_user($tutorial, $session, $user, $time = null) {
        global $DB;

        // Validate the method parameters.
        if (!isset($tutorial->id) || !isset($tutorial->course)) {
            throw new coding_exception('A valid tutorialbooking record must be passed to '
                    . 'mod_tutorialbooking_generator::signup_user $tutorial');
        }

        if (!isset($session->id) || !isset($session->tutorialid)) {
            throw new coding_exception('A valid session record must be passed to '
                    . 'mod_tutorialbooking_generator::signup_user $session');
        }

        if ($tutorial->id !== $session->tutorialid) {
            throw new coding_exception('The session record must be for the tutorial record passed to '
                    . 'mod_tutorialbooking_generator::signup_user $session');
        }

        if (!isset($user->id)) {
            throw new coding_exception('A valid user record must be passed to '
                    . 'mod_tutorialbooking_generator::signup_user $user');
        }

        // Create the signup record.
        $record = new stdClass();
        $record->userid = $user->id;
        $record->courseid = $tutorial->course;
        $record->tutorialid = $tutorial->id;
        $record->sessionid = $session->id;
        if (is_null($time)) {
            $record->signupdate = time();
        } else {
            $record->signupdate = $time;
        }
        $record->waiting = 0;
        $record->blocked = 0;

        $id = $DB->insert_record('tutorialbooking_signups', $record);
        return $DB->get_record('tutorialbooking_signups', array('id' => $id));
    }
}
