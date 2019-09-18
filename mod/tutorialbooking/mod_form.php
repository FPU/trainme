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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Class that defines the main tutorial editing form.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_mod_form extends moodleform_mod {
    /**
     * Defines forms elements.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('instanceheading', 'tutorialbooking'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('instancetitle', 'tutorialbooking'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 64), 'maxlength', 64, 'client');
        $mform->addElement('static', 'namehelp', '', get_string('instancenamehelp', 'tutorialbooking'));

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements(get_string('instancedesc', 'tutorialbooking'));
        $mform->addElement('static', 'namehelp', '', get_string('instancedeschelp', 'tutorialbooking'));

        // This is the lock dropdown.
        $mform->addElement('selectyesno', 'locked', get_string('lockedprompt', 'tutorialbooking'));
        $mform->setDefault('locked', get_config('tutorialbooking', 'defaultlock'));

        // Privacy options.
        $privacyoptions = array(
            mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS => get_string('privacy_showall', 'mod_tutorialbooking'),
            mod_tutorialbooking_tutorial::PRIVACY_SHOWOWN => get_string('privacy_showown', 'mod_tutorialbooking'),
        );
        $mform->addElement('select', 'privacy', get_string('privacy', 'mod_tutorialbooking'), $privacyoptions);
        $mform->setDefault('privacy', mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS);

        // If this is a new instance - force a save before allowing edit of timeslots.
        $mform->addElement('header', 'timeslot', get_string('timeslotheading', 'tutorialbooking'));

        if ($tbid = $this->current->id) { // If we are editing.
            // Display links to edit timeslots.
            $courseid = $this->current->course;
            $link = html_writer::link(
                new moodle_url("/mod/tutorialbooking/tutorialbooking_sessions.php?tutorialid=$tbid&courseid=$courseid"),
                get_string('linktomanagetext', 'tutorialbooking'));

            $mform->addElement('html', html_writer::tag('p', $link));
        } else {
            $mform->addElement('html', '<p style="text-align: center">'.get_string('notimeslotediting', 'tutorialbooking').'</p>');
        }

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Determines if a completion rule has been specified.
     *
     * @param array $data
     * @return bool true if completion is enabled
     */
    public function completion_rule_enabled($data) {
        return !empty($data['completionsignedup']);
    }

    /**
     * Adds custom completion rules to the form.
     *
     * @return string[] An array containing the names of the elements created for the completion rules.
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completionsignedup', '',
                get_string('completionsignedup', 'tutorialbooking'));
        $mform->addGroup($group, 'completionsignedupgroup', get_string('completionsignedupgroup', 'tutorialbooking'),
                array(' '), false);

        return array('completionsignedupgroup');
    }
}
