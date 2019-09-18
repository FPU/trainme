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
 * Defines the web service endpoint for getting the details of a Tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2017 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class details extends \external_api {
    /**
     * Gets the details of a tutorial booking activity.
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
        $signupslot = \mod_tutorialbooking_tutorial::get_signup($tutorial->id);
        $cm = get_coursemodule_from_instance('tutorialbooking', $tutorial->id, $tutorial->course);
        $context = \context_module::instance($cm->id);
        list($tutorial->intro, $tutorial->introformat) = external_format_text($tutorial->intro, $tutorial->introformat,
                $context->id, 'mod_tutorialbooking', 'intro', null);
        $return = array(
            'id' => $tutorial->id,
            'title' => $tutorial->name,
            'intro' => $tutorial->intro,
            'introformat' => $tutorial->introformat,
            'privacy' => $tutorial->privacy,
            'locked' => $tutorial->locked,
            'signedup' => ($signupslot !== false),
            'slots' => array(),
        );
        $slots = \mod_tutorialbooking_tutorial::gettutorialsessions($tutorial->id);
        $signups = \mod_tutorialbooking_tutorial::gettutorialsignups($tutorial->id);
        foreach ($slots as $slot) {
            if (!$slot->visible) {
                // Do not send hidden slots.
                continue;
            }
            if (isset($signups[$slot->id])) {
                $usedspaces = $signups[$slot->id]['total'];
            } else {
                $usedspaces = 0;
            }
            list($slot->description, $slot->descformat) = external_format_text($slot->description, $slot->descformat,
                $context->id, 'mod_tutorialbooking', null, null);
            list($slot->summary, $slot->summaryformat) = external_format_text($slot->summary, $slot->summaryformat,
                $context->id, 'mod_tutorialbooking', null, null);
            $return['slots'][] = array(
                'id' => $slot->id,
                'title' => $slot->description,
                'titleformat' => $slot->descformat,
                'summary' => $slot->summary,
                'summaryformat' => $slot->summaryformat,
                'location' => $slot->location,
                'spaces' => $slot->spaces,
                'usedspaces' => $usedspaces,
                'visible' => $slot->visible,
                'signedup' => ($slot->id == $signupslot),
            );
        }
        return $return;
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
        return new \external_function_parameters(array(
            'id' => new \external_value(PARAM_INT, 'The id of the activity', VALUE_REQUIRED),
            'title' => new \external_value(PARAM_TEXT, 'The name of the activity', VALUE_REQUIRED),
            'intro' => new \external_value(PARAM_RAW, 'The description of the activity', VALUE_REQUIRED),
            'introformat' => new \external_value(PARAM_INT, 'The format of the description', VALUE_REQUIRED),
            'privacy' => new \external_value(PARAM_INT, 'The privacy setting', VALUE_REQUIRED),
            'locked' => new \external_value(PARAM_BOOL, 'Indicates if the Tutorial booking is read only', VALUE_REQUIRED),
            'signedup' => new \external_value(PARAM_BOOL, 'Indicates if the user making the request has signed up to a slot', VALUE_REQUIRED),
            'slots' => new \external_multiple_structure(
                new \external_single_structure(array(
                    'id' => new \external_value(PARAM_INT, 'The id of the slot', VALUE_REQUIRED),
                    'title' => new \external_value(PARAM_RAW, 'The title of the slot', VALUE_REQUIRED),
                    'titleformat' => new \external_value(PARAM_INT, 'The format of the title', VALUE_REQUIRED),
                    'summary' => new \external_value(PARAM_RAW, 'A summary of the activities taking place on the slot', VALUE_REQUIRED),
                    'summaryformat' => new \external_value(PARAM_INT, 'The format of the summary', VALUE_REQUIRED),
                    'location' => new \external_value(PARAM_TEXT, 'The location the tutorial is taking place', VALUE_REQUIRED),
                    'spaces' => new \external_value(PARAM_INT, 'The total number of spaces', VALUE_REQUIRED),
                    'usedspaces' => new \external_value(PARAM_INT, 'The number of spaces used', VALUE_REQUIRED),
                    'visible' => new \external_value(PARAM_BOOL, 'Indicates if the slot is visible to users', VALUE_REQUIRED),
                    'signedup' => new \external_value(PARAM_BOOL, 'Indicates if the user making the request has signed up to this slot', VALUE_REQUIRED),
                ), 'The details of a Tutorial slot')
            ),
        ));
    }
}
