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

require_once("$CFG->libdir/formslib.php");

/**
 * Form for editing tutorial session information.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_session_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {

        global $USER, $OUTPUT, $PAGE;

        $mform = $this->_form;

        $mform->addElement('html', html_writer::tag('h2', $this->_customdata['title'], array('class' => "main help")));

        if (!$this->_customdata['id']) {
            $mform->addElement('header', 'session', get_string('newsessionheading', 'tutorialbooking'));
            $mform->addElement('static', 'sessionhelp', '', get_string('newsessionhelp', 'tutorialbooking',
                $this->_customdata['title']));
        } else {
            $mform->addElement('header', 'session', get_string('editsessionheading', 'tutorialbooking'));
            $mform->addElement('static', 'sessionhelp', '', get_string('editsessionhelp', 'tutorialbooking',
                $this->_customdata['title']));
        }

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'tutorialid', $this->_customdata['tutorialid']);
        $mform->setType('tutorialid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'sequence', $this->_customdata['sequence']);
        $mform->setType('sequence', PARAM_RAW);
        $mform->addElement('hidden', 'visible', $this->_customdata['visible']);
        $mform->setType('visible', PARAM_BOOL);
        $mform->addElement('hidden', 'usercreated', $this->_customdata['usercreated']);
        $mform->setType('usercreated', PARAM_RAW);
        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHA);

        // Description.
        $mform->addElement('textarea', 'description', get_string('sessiondescriptionprompt', 'tutorialbooking'));
        $mform->setType('description', PARAM_TEXT);
        if (isset($this->_customdata['description'])) {
            $mform->setDefault('description', $this->_customdata['description']);
        }

        // A formatable summary area.
        $mform->addElement('editor', 'summary', get_string('sessionsummaryprompt', 'tutorialbooking'));
        $mform->setType('summary', PARAM_RAW);
        if (isset($this->_customdata['summary'])) {
            $mform->setDefault('summary', $this->_customdata['summary']);
        }

        $mform->addElement('static', 'sessiondescriptionhelp', '',
                html_writer::tag('strong', get_string('sessiondescriptionhelp', 'tutorialbooking')));
        $mform->addElement('static', 'sessiondescriptionhelp2', '', get_string('sessiondescriptionhelp2', 'tutorialbooking'));

        // Spaces/places.
        $mform->addElement('text', 'spaces', get_string('spacesprompt', 'tutorialbooking'), array('size' => 3));
        $mform->setDefault('spaces', $this->_customdata['spaces']); // Default set in settings.
        $mform->setType('spaces', PARAM_INT);

        if (count($this->_customdata['positions'])) {
            $mform->addElement('select', 'newposition', get_string('positionprompt', 'tutorialbooking'),
                $this->_customdata['positions']);
            $mform->setDefault('newposition', $this->_customdata['sequence']); // Really want bottom of the page to be the default.
            $mform->setType('newposition', PARAM_INT);
        }

        // Buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'save',
                get_string('save', 'tutorialbooking'), array('id' => 'id_submitbutton'));
        $buttonarray[] = $mform->createElement('submit', 'saveasnew',
                get_string('saveasnew', 'tutorialbooking'), array('id' => 'id_submitbutton2'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel',
                get_string('cancel', 'tutorialbooking'), array('id' => 'id_cancel'));
        if (!empty($this->_customdata['id'])) { // Existing record.
            $buttonarray[] = $mform->createElement('reset', 'resetbutton', get_string('reset', 'tutorialbooking'));
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}
