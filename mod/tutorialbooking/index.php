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
 * List of tutorialbookings in course when directory is requested directly
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tutorialbooking\event\course_module_instance_list_viewed;

require_once('../../config.php');
require_once("$CFG->libdir/filelib.php");

$id = required_param('id', PARAM_INT);
$exportall = optional_param('exportall', 0, PARAM_INT);

// Get course details.
$course = get_course($id);

// Get the context of the course.
$context = context_course::instance($course->id);

require_course_login($course, true);

// Check if the user can export everything in the course.
$canexportall = has_capability('mod/tutorialbooking:exportallcoursetutorials', $context);

// Check if we should export all the records.
if ($exportall && $canexportall) {
    mod_tutorialbooking_export::exportcourse($course->id);
    die();
}

$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => context_course::instance($course->id)
);
$event = course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strurl = get_string('modulename', 'tutorialbooking');
$strurls = get_string('modulenameplural', 'tutorialbooking');
$strsectionname = get_string('sectionname', 'format_'.$course->format);
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url( new moodle_url(str_replace($CFG->wwwroot, '', strip_querystring(qualified_me()))),
        array('id' => $course->id));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('modulename', 'tutorialbooking'));
echo $OUTPUT->header();

if ($tutorials = get_all_instances_in_course('tutorialbooking', $course)) {
    if ($canexportall) {
        $url = new moodle_url('/mod/tutorialbooking/index.php', array('id' => $id, 'exportall' => 1));
        echo html_writer::tag('p', html_writer::link($url, get_string('exportcsvlistallprompt', 'mod_tutorialbooking')));
    }

    $usesections = course_format_uses_sections($course->format);

    $table = new html_table();
    $table->attributes['class'] = 'generaltable mod_index';

    if ($usesections) {
        $table->head = array ($strsectionname, $strname, $strintro);
        $table->align = array ('center', 'left', 'left');
    } else {
        $table->head = array ($strlastmodified, $strname, $strintro);
        $table->align = array ('left', 'left', 'left');
    }

    $modinfo = get_fast_modinfo($course);
    $currentsection = '';
    foreach ($tutorials as $tutorial) {
        $cm = $modinfo->cms[$tutorial->coursemodule];
        if ($usesections) {
            $printsection = '';
            if ($tutorial->section !== $currentsection) {
                if ($tutorial->section) {
                    $printsection = get_section_name($course, $tutorial->section);
                }
                if ($currentsection !== '') {
                    $table->data[] = 'hr';
                }
                $currentsection = $tutorial->section;
            }
        } else {
            $printsection = '<span class="smallinfo">'.userdate($tutorial->timemodified)."</span>";
        }

        $class = $tutorial->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
        $table->data[] = array (
            $printsection,
            "<a $class href=\"view.php?id=$cm->id\">".format_string($tutorial->name)."</a>",
            format_module_intro('tutorial', $tutorial, $cm->id));
    }

    echo html_writer::table($table);
    echo $OUTPUT->footer();

} else {
    notice(get_string('thereareno', 'tutorialbooking'), "$CFG->wwwroot/course/view.php?id=$course->id");
}
