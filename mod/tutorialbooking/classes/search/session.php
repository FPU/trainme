<?php
// This file is part of Moodle - http://moodle.org/
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
 * Search area for mod_tutorialbooking activity seesions.
 *
 * @package    mod_tutorialbooking
 * @copyright  2016 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking\search;

defined('MOODLE_INTERNAL') || die();

/**
 * Search area for mod_tutorialbooking activity sessions.
 *
 * @package    mod_tutorialbooking
 * @copyright  2016 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session extends \core_search\base_mod {
    /**
     * Returns a recordset with all required session information.
     *
     * @global \moodle_database $DB
     * @param int $modifiedfrom Return only records modified after this date
     * @param \context|null $context Context (null means no context restriction)
     * @return \moodle_recordset|null|false Recordset / null if no results / false if not supported
     */
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        global $DB;
        list ($contextjoin, $contextparams) = $this->get_context_restriction_sql(
                $context, 
                $this->get_module_name(), 
                't', 
                SQL_PARAMS_NAMED
        );
        if (is_null($contextjoin)) {
            return null;
        }
        $sql = "SELECT ts.*, t.course AS course "
                . "FROM {tutorialbooking} t "
                . "JOIN {tutorialbooking_sessions} ts ON ts.tutorialid = t.id "
                . "$contextjoin "
                . "WHERE ts.timemodified >= :timemodified";
        $params = array_merge($contextparams, array('timemodified' => $modifiedfrom));
        return $DB->get_recordset_sql($sql, $params);
    }

    /**
     * Returns the document for a particular session.
     *
     * @param stdClass $record
     * @param array $options
     * @return \core_search\document
     */
    public function get_document($record, $options = array()) {
        try {
            $cm = $this->get_cm('tutorialbooking', $record->tutorialid, $record->course);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $e) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving tutorialbooking_sessions ' .
                    $record->id . ' document, not all required data is available: ' .
                    $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $e) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving tutorialbooking_sessions ' .
                    $record->id . ' document: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($record->id, 'mod_tutorialbooking', 'session');
        $doc->set('title', content_to_text($record->description, $record->descformat));
        $doc->set('content', content_to_text($record->summary, $record->summaryformat));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $record->course);
        $doc->set('owneruserid', $record->usercreated);
        $doc->set('modified', $record->timemodified);

        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $record->timecreated)) {
            // If the document was created after the last index time, it must be new.
            $doc->set_is_new(true);
        }

        return $doc;
    }

    /**
     * Check that the user can view the session.
     *
     * @global \moodle_database $DB
     * @param int $id Session id.
     * @return int
     */
    public function check_access($id) {
        global $DB;
        try {
            // Ensure that the tutorial record exists.
            $sql = "SELECT t.id, t.course "
                    . "FROM {tutorialbooking} t "
                    . "JOIN {tutorialbooking_sessions} ts ON ts.tutorialid = t.id "
                    . "WHERE ts.id = :id";
            $tutorial = $DB->get_record_sql($sql, array('id' => $id), MUST_EXIST);
            $cminfo = $this->get_cm('tutorialbooking', $tutorial->id, $tutorial->course);
            $cminfo->get_course_module_record();
        } catch (\dml_missing_record_exception $e) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $e) {
            return \core_search\manager::ACCESS_DENIED;
        }

        // Recheck uservisible although it should have already been checked in core_search.
        if ($cminfo->uservisible === false) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    /**
     * Get a url for the session.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc) {
        return $this->get_context_url($doc);
    }

    /**
     * Get a url for the activity.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/tutorialbooking/view.php', array('id' => $contextmodule->instanceid));
    }
}
