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

namespace mod_tutorialbooking\task;

defined('MOODLE_INTERNAL') || die();

/**
 * CRON task to remove duplicate signups.
 *
 * Checks to see if any users where added to a tutorial booking activity more than one time.
 * If it finds any it will delete all but one of their records. It should be rare that it finds anything to delete as
 * it can only be caused by hopefully rare race conditions.
 *
 * @package   mod_tutorialbooking
 * @category  task
 * @copyright 2014 University of Nottingham
 * @author    Neill Magill <neill.magill@nottingham.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fix_duplicates extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cronfixduplicates', 'mod_tutorialbooking');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     *
     * @global moodle_database $DB
     * @return void
     */
    public function execute() {
        global $DB;
        // Find all users who are on either more than one session, or on a session more than one time for a tutorial booking activity.
        $sql = 'SELECT userid, tutorialid, COUNT(id) AS total '
                . 'FROM {tutorialbooking_signups} '
                . 'GROUP BY tutorialid, userid '
                . 'HAVING COUNT(id) > 1';
        $problems = $DB->get_recordset_sql($sql);
        $problemusers = 0;
        foreach ($problems as $problem) {
            $params = array(
                'userid' => $problem->userid,
                'tutorialid' => $problem->tutorialid,
            );
            // We want all but the first record.
            $records = $DB->get_records('tutorialbooking_signups', $params, '', 'id', 1, $problem->total);
            $ids = array();
            foreach ($records as $record) {
                $ids[] = $record->id;
            }
            // Find all the records we want to delete. We want all but one record.
            list($idsql, $idparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'id');
            $sql = "id $idsql";
            $DB->delete_records_select('tutorialbooking_signups', $sql, $idparams);
            $problemusers++;
        }
        mtrace('Fixed '.$problemusers.' instances of user duplication.');
        $problems->close();
    }
}
