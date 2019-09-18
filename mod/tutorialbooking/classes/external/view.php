<?php
// This file is part of the tutorial booking plugin
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
//

namespace mod_tutorialbooking\external;
use mod_tutorialbooking\event\course_module_viewed;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Logs that a user viewed a Tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2017 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view extends \external_api {
    /**
     * Logs that a user viewed a Tutorial booking activity.
     *
     * @global \moodle_database $DB
     * @param int $id The id of a tutorial booking activity.
     * @return array
     */
    public static function view($id) {
        global $DB;
        $params = self::validate_parameters(self::view_parameters(), array('id' => $id));
        // Get the tutrialbooking activity and check that the user should have access to it.
        $tutorial = $DB->get_record('tutorialbooking', array('id' => $params['id']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($tutorial, 'tutorialbooking');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        $warnings = array();
        // Mark viewed if required.
        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm);
        // Trigger a view event.
        $eventdata = array(
            'context' => $context,
            'objectid' => $tutorial->id,
        );
        $event = course_module_viewed::create($eventdata);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('tutorialbooking', $tutorial);
        $event->trigger();
        // Generate the result.
        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Defines the inputs for the web service method.
     *
     * @return \external_function_parameters
     */
    public static function view_parameters() {
        return new \external_function_parameters(array(
            'id' => new \external_value(PARAM_INT, 'The instance id of a Tutorial booking activity', VALUE_REQUIRED),
        ));
    }

    /**
     * Defines the output of the web service.
     *
     * @return \external_function_parameters
     */
    public static function view_returns() {
        return new \external_single_structure(
            array(
                'status' => new \external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new \external_warnings()
            )
        );
    }
}
