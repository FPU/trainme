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

/**
 * Define all the restore steps that will be used by the restore_tutorialbooking_activity_task.
 *
 * @package   mod_tutorialbooking
 * @category  backup
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one tutorialbooking activity.
 *
 * @author    Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @copyright University of Nottingham, 2012
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_tutorialbooking_activity_structure_step extends restore_activity_structure_step {
    /**
     * Function that will return the structure to be processed by this restore_step.
     * Must return one array of @restore_path_element elements
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('tutorialbooking', '/activity/tutorialbooking');
        $paths[] = new restore_path_element('tutorialbooking_sessions', '/activity/tutorialbooking/sessions/session');
        if ($userinfo) {
            $paths[] = new restore_path_element('tutorialbooking_signups',
                '/activity/tutorialbooking/sessions/session/signups/signup');
            $paths[] = new restore_path_element('tutorialbooking_messages', '/activity/tutorialbooking/messages/message');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a tutorial booking.
     *
     * @global moodle_database $DB
     * @param array $data
     */
    protected function process_tutorialbooking($data) {
        global $DB;

        $userinfo = $this->get_setting_value('userinfo');

        $data = (object)$data;
        $data->course = $this->get_courseid();
        if (!$userinfo) { // Only update these if user information is not being imported.
            $data->timecreated = $this->apply_date_offset($data->timecreated);
            $data->timemodified = $this->apply_date_offset($data->timemodified);
        }

        // Insert the tutorialbooking record.
        $newitemid = $DB->insert_record('tutorialbooking', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Tutorialbooking_sessions.
     *
     * @global moodle_database $DB
     * @global stdClass $USER
     * @param array $data
     */
    protected function process_tutorialbooking_sessions($data) {
        global $DB, $USER;

        $userinfo = $this->get_setting_value('userinfo');

        $data = (object)$data;
        $oldid = $data->id;

        $data->tutorialid = $this->get_new_parentid('tutorialbooking');
        if (!$userinfo) { // Only update these if user information is not being imported.
            $data->timecreated = $this->apply_date_offset($data->timecreated);
            $data->timemodified = $this->apply_date_offset($data->timemodified);
        }
        // Since we cannot annotate the usercreated, use the current user's details.
        $data->usercreated = $USER->id;

        // Insert the entry record.
        $newitemid = $DB->insert_record('tutorialbooking_sessions', $data);
        $this->set_mapping('session', $oldid, $newitemid);
    }

    /**
     * Tutorialbooking_signups.
     * Only called if user details required.
     *
     * @global moodle_database $DB
     * @param array $data
     */
    protected function process_tutorialbooking_signups($data) {
        global $DB;

        $data = (object)$data;

        $data->userid = $this->get_mappingid('user', $data->userid); // This has been annotated - user should be in enrolment.
        $data->courseid = $this->get_courseid();
        $data->tutorialid = $this->get_new_parentid('tutorialbooking');
        // This should work but does not.
        $data->sessionid = $this->get_mappingid('session', $data->sessionid);

        // Due to not being able to annotate blockers - we remove any blocker information.
        unset($data->blockerid);
        unset($data->blockdate);

        $DB->insert_record('tutorialbooking_signups', $data);
    }

    /**
     * Restore tutorial booking messages.
     *
     * @global moodle_database $DB
     * @param array $data
     */
    protected function process_tutorialbooking_messages($data) {
        global $DB;

        $data = (object)$data;
        $data->tutorialbookingid = $this->get_new_parentid('tutorialbooking');
        $data->sentby = $this->get_mappingid('user', $data->sentby);
        // The sentto information is serialised user id's so we need to update them seperatly.
        $sentto = unserialize($data->sentto);
        foreach ($sentto as &$user) {
            $user = $this->get_mappingid('user', $user);
        }
        $data->sentto = serialize($sentto);

        $DB->insert_record('tutorialbooking_messages', $data);
    }
}
