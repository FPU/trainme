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

/**
 * Setup the webservices for the plugin.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2017 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_tutorialbooking_capabilities' => array(
        'classname' => 'mod_tutorialbooking\external\capabilities',
        'methodname' => 'get',
        'classpath' => '/mod/tutorialbooking/classes/external/capabilities.php',
        'description' => "Retrives the capabilities of a user for a tutorialbooking activity",
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
    'mod_tutorialbooking_details' => array(
        'classname' => 'mod_tutorialbooking\external\details',
        'methodname' => 'view',
        'classpath' => '/mod/tutorialbooking/classes/external/details.php',
        'description' => 'Get the details of a Tutorialbooking activity',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
    'mod_tutorialbooking_moveslot' => array(
        'classname' => 'mod_tutorialbooking\external\moveslot',
        'methodname' => 'move',
        'classpath' => '/mod/tutorialbooking/classes/external/moveslot.php',
        'description' => "Moves a tutorial booking slot",
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_tutorialbooking_remove_signup' => array(
        'classname' => 'mod_tutorialbooking\external\removesignup',
        'methodname' => 'remove_signup',
        'classpath' => '/mod/tutorialbooking/classes/external/removesignup.php',
        'description' => "Remove the user's signup from a Tutorial booking activity slot",
        'type' => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
    'mod_tutorialbooking_signup' => array(
        'classname' => 'mod_tutorialbooking\external\signup',
        'methodname' => 'signup',
        'classpath' => '/mod/tutorialbooking/classes/external/signup.php',
        'description' => 'Sign the user up to a Tutorial booking activity slot',
        'type' => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
    'mod_tutorialbooking_view_tutorialbooking' => array(
        'classname' => 'mod_tutorialbooking\external\view',
        'methodname' => 'view',
        'classpath' => '/mod/tutorialbooking/classes/external/view.php',
        'description' => "Logs that the user viewed the tutorial booking activity",
        'type' => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
);
