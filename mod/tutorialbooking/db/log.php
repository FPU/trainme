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

defined('MOODLE_INTERNAL') || die();

/*
 * Definition of log entries.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$logs = array(
    array('module' => 'tutorialbooking', 'action' => 'addsession', 'mtable' => 'tutorialbooking_sessions', 'field' => 'description'),
    array('module' => 'tutorialbooking', 'action' => 'updatesession', 'mtable' => 'tutorialbooking_sessions', 'field' => 'description'),
    array('module' => 'tutorialbooking', 'action' => 'deletesession', 'mtable' => 'tutorialbooking_sessions', 'field' => 'description'),
    array('module' => 'tutorialbooking', 'action' => 'editview', 'mtable' => 'tutorialbooking_sessions', 'field' => 'description'),
    array('module' => 'tutorialbooking', 'action' => 'view', 'mtable' => 'tutorialbooking_sessions', 'field' => 'description'),
    array('module' => 'tutorialbooking', 'action' => 'view all', 'mtable' => 'tutorialbooking_sessions', 'field' => 'description'),
    array('module' => 'tutorialbooking', 'action' => 'notify group', 'mtable' => 'user', 'field' => 'username'),
    array('module' => 'tutorialbooking', 'action' => 'signup', 'mtable' => 'user', 'field' => 'username'),
    array('module' => 'tutorialbooking', 'action' => 'unsignup', 'mtable' => 'user', 'field' => 'username'),
    array('module' => 'tutorialbooking', 'action' => 'remove signup', 'mtable' => 'user', 'field' => 'username'),
    array('module' => 'tutorialbooking', 'action' => 'add signup', 'mtable' => 'user', 'field' => 'username'),
);
