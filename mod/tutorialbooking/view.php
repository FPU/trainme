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
 * Regular viewer of tutorial bookings
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tutorialbooking\renderable\tutorialbooking;
use mod_tutorialbooking\event\course_module_viewed;

require('../../config.php');

$id = optional_param('id', 0, PARAM_INT);// Course module ID.
$tutorialid = optional_param('tutorialid', 0, PARAM_INT); // URL instance id.
$redirect = optional_param('redirect', 1, PARAM_BOOL); // If set to 0 do not automatically redirect editors.
$action = optional_param('action', null, PARAM_TEXT);

// Determine the data we need from what we've been passed.
if ($tutorialid) { // Two ways to specify the module.
    list($course, $cm) = get_course_and_cm_from_instance($tutorialid, 'tutorialbooking');
} else {
    list($course, $cm) = get_course_and_cm_from_cmid($id, 'tutorialbooking');
}
$tutorial = $DB->get_record('tutorialbooking', array('id' => $cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Array of paramerters sent to the page.
$pageparams = array('id' => $cm->id, 'redirect' => $redirect);

if (has_capability('mod/tutorialbooking:viewadminpage', $context) && $redirect == 1) {
    // If an editor auto redirect to the admin page.
    redirect(new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php',
            array('tutorialid' => $PAGE->cm->instance,
                'courseid' => $PAGE->course->id)));
}

$PAGE->set_url(new moodle_url('/mod/tutorialbooking/view.php'), $pageparams); // Point to this page.
$PAGE->set_context($context);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('incourse');
// Page setup stuff.
$PAGE->set_title(get_string('pagetitle', 'tutorialbooking'));
$PAGE->navbar->add(get_string('pagecrumb', 'tutorialbooking'));

$output = $PAGE->get_renderer('mod_tutorialbooking');

// Mark viewed if required.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// If there is an action - do it then redirect back to this page excluding action params.
if (!empty($action)) {
    if ($action == 'signup') {
        $sessionid = required_param('sessionid', PARAM_INT);
        $session = $DB->get_record('tutorialbooking_sessions', array('id' => $sessionid));
        mod_tutorialbooking_user::adduser($USER->id, $session, $tutorial, $context, $completion, $cm, $PAGE->url, true);
        redirect($PAGE->url);
    } else if ($action == 'remove') {
        $output->delete_signup_confirm($cm);
    } else if ($action == 'confirmedremove') {
        require_sesskey();
        mod_tutorialbooking_user::remove_user($USER->id, $tutorial, $completion, $cm);
        redirect($PAGE->url);
    }
} else {
    $PAGE->force_settings_menu(true);
    $eventdata = array(
        'context' => $context,
        'objectid' => $tutorial->id,
    );
    $event = course_module_viewed::create($eventdata);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('tutorialbooking', $tutorial);
    $event->trigger();
    // Display the default page.
    $output->render_tutorialbooking(new tutorialbooking($tutorial));
}
