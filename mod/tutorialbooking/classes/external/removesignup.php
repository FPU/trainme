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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Defines the web service endpoint for removing a sign up from a Tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2017 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class removesignup extends \external_api {
    /**
     * Remove the users sign up from the tutorial booking the slot is in.
     *
     * @global \moodle_database $DB
     * @global \stdClass $USER
     * @param int $slotid The id of a Tutorial booking slot.
     * @return array
     */
    public static function remove_signup($slotid) {
        global $DB, $USER;
        $params = self::validate_parameters(self::remove_signup_parameters(), array('slotid' => $slotid));
        $return = array();
        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $params['slotid']), '*', MUST_EXIST);
        $tutorial = $DB->get_record('tutorialbooking', array('id' => $session->tutorialid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($tutorial->id, 'tutorialbooking');
        $context = \context_module::instance($cm->id);
        // Initialise the completion.
        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm);
        try {
            \mod_tutorialbooking_user::remove_user(
                    $USER->id,
                    $tutorial,
                    $completion,
                    $cm,
                    false
            );
            $return = array(
                'success' => true,
                'error' => array(),
            );
        } catch (\Exception $e) {
            $return = array(
                'success' => false,
                'error' => array(
                    'message' => $e->getMessage(),
                ),
            );
        }
        return $return;
    }

    /**
     * Defines the inputs for the web service method.
     *
     * @return \external_function_parameters
     */
    public static function remove_signup_parameters() {
        return new \external_function_parameters(array(
            'slotid' => new \external_value(PARAM_INT, 'The id of a Tutorial booking slot', VALUE_REQUIRED),
        ));
    }

    /**
     * Defines the output of the web service.
     *
     * @return \external_function_parameters
     */
    public static function remove_signup_returns() {
        return new \external_function_parameters(array(
            'success' => new \external_value(PARAM_BOOL, 'Returns is the removal was a success', VALUE_REQUIRED),
            'error' => new \external_single_structure(array(
                'message' => new \external_value(PARAM_TEXT, 'Details about any sign up failure reason', VALUE_OPTIONAL),
            ), 'Details about any errors'),
        ));
    }
}
