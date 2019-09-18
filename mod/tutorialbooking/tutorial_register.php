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
 * print tutorial register - opens in new page/tab
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php'); // This works everytime - the one in the coding guide does not if moodle is not in the root.
global $CFG, $DB;

require_once($CFG->dirroot.'/course/lib.php'); // Do I need this?

// These checks will generate exceptions if they do not pass.
$sessionid = required_param('sessionid', PARAM_INT); // Plugin instance.
$session = $DB->get_record('tutorialbooking_sessions', array('id' => $sessionid), '*', MUST_EXIST);

$courseid = required_param('courseid', PARAM_INT); // Course.
list($course, $cm) = get_course_and_cm_from_instance($session->tutorialid, 'tutorialbooking', $courseid);
$tutorialbookingcontext = context_module::instance($cm->id);

$format = optional_param('format', mod_tutorialbooking_register::ORDER_NAME, PARAM_INT); // Default is by name.

require_course_login($course, false, $cm);

$output = $PAGE->get_renderer('mod_tutorialbooking');

// The user must have permission to view the register.
require_capability('mod/tutorialbooking:printregisters', $tutorialbookingcontext);

// Create the register table.
$registertable = new html_table();
$registertable->attributes['class'] = 'tutorialbooking register';
$registertable->width = '100%';
$registertable->attributes['style'] = 'border: thin gray solid;';
$registertable->data = array();

$coursename = new html_table_cell($course->fullname);
$coursename->colspan = 2;
$coursename->style = 'text-align:center;font-weight:bold;font-size:2em;margin:3px;padding:5px;border: thin gray solid;';
$registertable->data[] = new html_table_row(array($coursename));

$sessionname = new html_table_cell($session->description);
$sessionname->colspan = 2;
$sessionname->style = 'text-align:center;font-weight:bold;margin:3px;padding:5px;border: thin gray solid;';
$registertable->data[] = new html_table_row(array($sessionname));

$dateline = new html_table_cell(get_string('registerdateline', 'tutorialbooking'));
$dateline->colspan = 2;
$dateline->style = 'text-align:center;font-weight:bold;margin:3px;padding:5px;border: thin gray solid;';
$registertable->data[] = new html_table_row(array($dateline));

$studentcol = new html_table_cell(get_string('studentcoltitle', 'tutorialbooking'));
$studentcol->style = 'text-align:center;width:50%;font-weight:bold;margin:3px;padding:5px;border: thin gray solid;';
$attendcole = new html_table_cell(get_string('attendcoltitle', 'tutorialbooking'));
$attendcole->style = 'text-align:center;font-weight:bold;margin:3px;padding:5px;border: thin gray solid;';
$registertable->data[] = new html_table_row(array($studentcol, $attendcole));

// Student list.
$signedup = mod_tutorialbooking_register::getsessionsignups($sessionid, $format);
$emptycell = new html_table_cell('&nbsp;');
$emptycell->style = 'margin:3px;padding:5px;border: thin gray solid;';
foreach ($signedup as $signup) {
    $studentname = new html_table_cell("$signup->lastname, $signup->firstname");
    $studentname->style = 'margin:3px;padding:5px;border: thin gray solid;';
    $registertable->data[] = new html_table_row(array($studentname, $emptycell));
}

$footer = new html_table_cell(get_string('registerfooter', 'tutorialbooking'));
$footer->colspan = 2;
$footer->style = 'margin:3px;padding:15px 5px;border: thin gray solid;';
$registertable->data[] = new html_table_row(array($footer));

// Print the register to screen.
$output->render_register($registertable);
