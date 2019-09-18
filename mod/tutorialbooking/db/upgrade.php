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
 * Upgrade functionality
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute tutorialbooking upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_tutorialbooking_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2012062705) {

        // Define field courseid to be renamed as course.
        $table = new xmldb_table('tutorialbooking');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $newname = 'course'; // Hate this, hate it!
        $dbman->rename_field($table, $field, $newname, true, true);

        upgrade_mod_savepoint(true, 2012062705, 'tutorialbooking');
    }

    if ($oldversion < 2012100401) {
        // Find all sessions that do not have a max sequence equal to the number of sessions.
        $sql = "SELECT t.tutorialid FROM {tutorialbooking_sessions} t GROUP BY t.tutorialid HAVING MAX(t.sequence) != COUNT(*)";
        $result = $DB->get_recordset_sql($sql);

        foreach ($result as $brokentutorialid) {
            // Reset the sequence counter.
            $counter = 1;

            // Get all the sessions for this tutorial.
            $params['tutorialid'] = $brokentutorialid->tutorialid;
            $sql = "SELECT t.id, t.sequence FROM {tutorialbooking_sessions} t WHERE tutorialid = :tutorialid";
            $sessions = $DB->get_recordset_sql($sql, $params);

            foreach ($sessions as $session) { // Fix the sequence.
                $sql = "UPDATE {tutorialbooking_sessions} SET sequence = $counter WHERE id = ".$session->id;
                $DB->execute($sql);
                // Increment the sequence counter.
                $counter++;
            }
        }

        upgrade_mod_savepoint(true, 2012100401, 'tutorialbooking');
    }

    if ($oldversion < 2013051000) {

        $table = new xmldb_table('tutorialbooking_messages');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('tutorialbookingid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('sentby', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('senttime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sentto', XMLDB_TYPE_BINARY, 'big', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);

        $table->add_key('tutorial_booking_messages_id', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('tutorialid_index', XMLDB_INDEX_NOTUNIQUE, array('tutorialbookingid'));
        $table->add_index('userfrom_index', XMLDB_INDEX_NOTUNIQUE, array('sentby'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table); // Create the table if it isn't there.
        }

        upgrade_mod_savepoint(true, 2013051000, 'tutorialbooking');
    }

    if ($oldversion < 2013052801) {
        // Add a field to allow completion based on a user having signed up to a tutorial slot.
        $table = new xmldb_table('tutorialbooking');
        $field = new xmldb_field('completionsignedup', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2013052801, 'tutorialbooking');
    }

    if ($oldversion < 2013080100) {
        $table = new xmldb_table('tutorialbooking');
        $field = new xmldb_field('privacy', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'completionsignedup');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2013080100, 'tutorialbooking');
    }

    if ($oldversion < 2015041700) {
        $table = new xmldb_table('tutorialbooking_sessions');
        // Add the new summary field.
        $field = new xmldb_field('summary', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'descformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Add the field to hold the formatting of the field.
        $field = new xmldb_field('summaryformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '0', 'summary');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2015041700, 'tutorialbooking');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
