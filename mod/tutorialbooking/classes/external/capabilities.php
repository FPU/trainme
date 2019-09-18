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
 * External service that gets the capabilities of the user on a specific Tutorial booking.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2017 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class capabilities extends \external_api {
    /**
     * Checks the Tutorial booking related capabilities of the user.
     *
     * @param int $id The id of a tutorial booking activity
     * @return array
     */
    public static function get($id) {
        $params = self::validate_parameters(self::get_parameters(), array('id' => $id));
        list($course, $cm) = get_course_and_cm_from_instance($params['id'], 'tutorialbooking');
        $context = \context_module::instance($cm->id);
        return array(
            'submit' => has_capability('mod/tutorialbooking:submit', $context),
            'removeuser' => has_capability('mod/tutorialbooking:removeuser', $context),
            'adduser' => has_capability('mod/tutorialbooking:adduser', $context),
            'oversubscribe' => has_capability('mod/tutorialbooking:oversubscribe', $context),
            'viewadminpage' => has_capability('mod/tutorialbooking:viewadminpage', $context),
            'editsignuplist' => has_capability('mod/tutorialbooking:editsignuplist', $context),
            'export' => has_capability('mod/tutorialbooking:export', $context),
            'message' => has_capability('mod/tutorialbooking:message', $context),
            'printregisters' => has_capability('mod/tutorialbooking:printregisters', $context),
            'viewallmessages' => has_capability('mod/tutorialbooking:viewallmessages', $context),
        );
    }

    /**
     * Defines the inputs for the web service method.
     *
     * @return \external_function_parameters
     */
    public static function get_parameters() {
        return new \external_function_parameters(array(
            'id' => new \external_value(PARAM_INT, 'The instance id of a Tutorial booking activity', VALUE_REQUIRED),
        ));
    }

    /**
     * Defines the output of the web service.
     *
     * @return \external_function_parameters
     */
    public static function get_returns() {
        return new \external_function_parameters(array(
            'submit' => new \external_value(PARAM_BOOL, 'Sign up to slots', VALUE_REQUIRED),
            'removeuser' => new \external_value(PARAM_BOOL, 'Remove other users from slots', VALUE_REQUIRED),
            'adduser' => new \external_value(PARAM_BOOL, 'Add other users to slots', VALUE_REQUIRED),
            'oversubscribe' => new \external_value(PARAM_BOOL, 'Sign up other users even if a slot is full', VALUE_REQUIRED),
            'viewadminpage' => new \external_value(PARAM_BOOL, 'Do admin activities in a tutorial booking activity', VALUE_REQUIRED),
            'editsignuplist' => new \external_value(PARAM_BOOL, 'Modify the details of the slots', VALUE_REQUIRED),
            'export' => new \external_value(PARAM_BOOL, 'Eport the tutorial booking', VALUE_REQUIRED),
            'message' => new \external_value(PARAM_BOOL, 'Send messages to users', VALUE_REQUIRED),
            'printregisters' => new \external_value(PARAM_BOOL, 'Print a register of users', VALUE_REQUIRED),
            'viewallmessages' => new \external_value(PARAM_BOOL, 'View all messages sent (rather than just the ones they sent)', VALUE_REQUIRED),
        ));
    }
}
