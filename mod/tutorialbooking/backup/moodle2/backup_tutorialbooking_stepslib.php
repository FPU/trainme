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
 * Define all the backup steps that will be used by the backup_tutorialbooking_activity_task.
 *
 * @package   mod_tutorialbooking
 * @category  backup
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete tutorialbooking structure for backup, with file and id annotations.
 *
 * @author    Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @copyright University of Nottingham, 2012
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_tutorialbooking_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the structure of a tutorial booking backup.
     *
     * @return backup_nested_element the $activitystructure wrapped by the common 'activity' element
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.

        // Tutorialbooking.
        $tutorialbooking = new backup_nested_element('tutorialbooking', array('id'),
            array(
                'name',
                'intro',
                'introformat',
                'locked',
                'timecreated',
                'timemodified',
                'completionsignedup',
                'privacy'
            )
        );

        // Tutorialbooking_sessions.
        $sessions = new backup_nested_element('sessions');
        $session = new backup_nested_element('session', array('id'),
            array(
                'description',
                'descformat',
                'location',
                'spaces',
                'sequence',
                'visible',
                'usercreated',
                'timecreated',
                'timemodified'
            )
        );

        // Tutorialbooking_signups.
        $signups = new backup_nested_element('signups');
        $signup = new backup_nested_element('signup', array('id'),
            array(
                'userid',
                'sessionid',       // Should be needed because of structure but restore has bug??
                'signupdate',
                'waiting',
                'blocked',
                'blockerid',
                'blockdate'
            )
        );

        $messages = new backup_nested_element('messages');
        $message = new backup_nested_element('message', array('id'),
            array(
                'tutorialbookingid',
                'sentby',
                'senttime',
                'subject',
                'sentto',
                'message'
            )
        );

        // Build the tree.
        $tutorialbooking->add_child($sessions);
        $sessions->add_child($session);

        $session->add_child($signups);
        $signups->add_child($signup);

        $tutorialbooking->add_child($messages);
        $messages->add_child($message);

        // Define sources.
        $tutorialbooking->set_source_table('tutorialbooking', array('id' => backup::VAR_ACTIVITYID));
        $session->set_source_table('tutorialbooking_sessions', array('tutorialid' => backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $signup->set_source_table('tutorialbooking_signups',
                array(
                    'sessionid' => backup::VAR_PARENTID,
                )
            );

            $message->set_source_table('tutorialbooking_messages', array('tutorialbookingid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $signup->annotate_ids('user', 'userid');
        $message->annotate_ids('user', 'sentby');
        $session->annotate_ids('user', 'usercreated');
        $signup->annotate_ids('user', 'blockerid');

        // Define file annotations.

        // Return the root element (tutorialbooking), wrapped into standard activity structure.
        return $this->prepare_activity_structure($tutorialbooking);
    }
}
