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
 * Class for exporting data from a tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_export {
    /** String required at the start of a file to identify the content as UTF 8. */
    const UTF8_IDENTIFIER = "\xEF\xBB\xBF";

    /**
     * Generates a file for exporting data from the tutorial booking activity in a rather strange format.
     *
     * @param int $tutorialid The id of the tutorial the export should be for.
     * @return void
     */
    public static function export($tutorialid) {
        $records = self::getexport($tutorialid);
        $filecontents = array();
        $filecontents[] = get_string('timeslottitle', 'mod_tutorialbooking')
                . '�' . get_string('idnumber', 'mod_tutorialbooking')
                . '�' . get_string('username', 'mod_tutorialbooking')
                . '�' . get_string('realname', 'mod_tutorialbooking')
                . '�' . get_string('coursefullname', 'mod_tutorialbooking'); // Titleline.
        foreach ($records as $record) {
            // Had to include id field to stop warning and exit of script.
            unset($record->id); // Needed this to keep moodle happy.
            $record->description = strip_tags($record->description);
            $record->realname = "{$record->lastname}, {$record->firstname}";
            unset($record->firstname);
            unset($record->lastname);
            unset($record->firstnamephonetic);
            unset($record->lastnamephonetic);
            unset($record->middlename);
            unset($record->alternatename);
            $record = (array) $record;
            $filecontents[] = implode('�', array_values($record));
        }
        send_temp_file(self::UTF8_IDENTIFIER.implode("\n", $filecontents), 'tutorial_export.txt', true);
    }

    /**
     * Generates a comma separated file to export the signups for all tutorial booking activities on a course.
     *
     * @param int $courseid The id of the course the file is for.
     * @return void
     */
    public static function exportcourse($courseid) {
        $records = self::getexportcourse($courseid);
        $filecontents = array();
        $filecontents[] = '"' . get_string('tutorialbooking', 'mod_tutorialbooking')
                . '","' . get_string('timeslottitle', 'mod_tutorialbooking')
                . '","' . get_string('idnumber')
                . '","' . get_string('username')
                . '","' . get_string('fullname')
                . '","' . get_string('coursefullname', 'mod_tutorialbooking') . '"'; // Titleline.
        foreach ($records as $record) {
            // Had to include id field to stop warning and exit of script.
            unset($record->id); // Needed this to keep moodle happy.
            $record->description = strip_tags($record->description);
            $record->realname = fullname($record);
            unset($record->firstname);
            unset($record->lastname);
            unset($record->firstnamephonetic);
            unset($record->lastnamephonetic);
            unset($record->middlename);
            unset($record->alternatename);
            $record = (array) $record;
            $filecontents[] = '"'.implode('","', array_values($record)).'"';
        }
        send_temp_file(self::UTF8_IDENTIFIER.implode("\n", $filecontents), 'tutorial_export.csv', true);
    }

    /**
     * Generates a comma separated file to export the signups for a tutorial booking activities.
     *
     * @param int $tutorialid The id of the tutorial the export should be for.
     * @return void
     */
    public static function exportcsv($tutorialid) {
        $records = self::getexport($tutorialid);
        $filecontents = array();
        $filecontents[] = '"' . get_string('timeslottitle', 'mod_tutorialbooking')
                . '","' . get_string('idnumber')
                . '","' . get_string('username')
                . '","' . get_string('fullname')
                . '","' . get_string('fullnamecourse') . '"'; // Titleline.
        foreach ($records as $record) {
            // Had to include id field to stop warning and exit of script.
            unset($record->id); // Needed this to keep moodle happy.
            $record->description = strip_tags($record->description);
            $record->realname = fullname($record);
            unset($record->firstname);
            unset($record->lastname);
            unset($record->firstnamephonetic);
            unset($record->lastnamephonetic);
            unset($record->middlename);
            unset($record->alternatename);
            $record = (array) $record;
            $filecontents[] = '"'.implode('","', array_values($record)).'"';
        }
        send_temp_file(self::UTF8_IDENTIFIER.implode("\n", $filecontents), 'tutorial_export.csv', true);
    }

    /**
     * Get a list of people that have signed upto tutorial slots.
     *
     * @param int $tutorialid The tutorial id.
     * @return stdClass[]|false Array of database records, or false if none are found.
     */
    protected static function getexport($tutorialid) {
        global $DB;
        $sql = "SELECT sup.id, ses.description, u.idnumber, u.username, NULL AS realname, "
                . "u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, c.fullname "
                . "FROM {course} c, {tutorialbooking} t, {tutorialbooking_sessions} ses, {tutorialbooking_signups} sup, {user} u "
                . "WHERE ses.tutorialid = t.id "
                . "AND sup.sessionid = ses.id "
                . "AND sup.userid = u.id "
                . "AND sup.tutorialid = :tutorialid "
                . "AND c.id = t.course "
                . "ORDER BY realname";

        return $DB->get_records_sql($sql, array('tutorialid' => $tutorialid));
    }

    /**
     * Get a list of all tutorial slot signups on a course.
     *
     * @param int $courseid The course id.
     * @return stdClass[]|false Array of database records, or false if none are found.
     */
    protected static function getexportcourse($courseid) {
        global $DB;
        $sql = "SELECT sup.id, t.name, ses.description, u.idnumber, u.username, NULL AS realname, "
                . "u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, c.fullname "
                . "FROM {course} c, {tutorialbooking} t, {tutorialbooking_sessions} ses, {tutorialbooking_signups} sup, {user} u "
                . "WHERE ses.tutorialid = t.id "
                . "AND sup.sessionid = ses.id "
                . "AND sup.userid = u.id "
                . "AND sup.courseid = ? "
                . "AND c.id = t.course "
                . "ORDER BY realname";

        return $DB->get_records_sql($sql, array($courseid));
    }
}
