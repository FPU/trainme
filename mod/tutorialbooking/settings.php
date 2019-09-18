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
 * Global settings/config for tutorial booking module
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Default numbers.
    $settings->add(new admin_setting_configtext('tutorialbooking/defaultnumbers',
        get_string('adminnumbersprompt', 'tutorialbooking'), get_string('adminnumbershelp', 'tutorialbooking'), 30, PARAM_INT));

    // Default locked status.
    $settings->add(new admin_setting_configselect('tutorialbooking/defaultlock',
            get_string('adminlockprompt', 'tutorialbooking'),
            get_string('adminlockhelp', 'tutorialbooking'),
            null,
            array(0 => 'Unlocked', 1 => 'Locked'),
            PARAM_INT));


    // Switch on live service.
    $settings->add(new admin_setting_configcheckbox('tutorialbooking/liveservice',
        get_string('adminserviceprompt', 'tutorialbooking'), get_string('adminservicehelp', 'tutorialbooking'), 0, PARAM_INT));

    // Add in our default settings for the stuff commented out above..
    set_config('iconson', 0, 'tutorialbooking');
    set_config('blockingon', 0, 'tutorialbooking');
    set_config('waitingon', 0, 'tutorialbooking');

}
