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
//

defined('MOODLE_INTERNAL') || die();

/*
 * Lists the observer functions for Moodle Events that this plugin uses.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2014 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$observers = array(
    array(
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => 'mod_tutorialbooking_observer::user_unenrolled',
        'internal' => true,
    ),

    array(
        'eventname' => '\core\event\role_unassigned',
        'callback' => 'mod_tutorialbooking_observer::role_unassigned',
        'internal' => true,
    ),
);
