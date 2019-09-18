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
 * Class that defines the Email form for tutorial booking.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_email_form extends moodleform {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER, $OUTPUT, $PAGE;

        $mform = $this->_form;

        $mform->addElement('html', html_writer::tag('h2', get_string('emailpagetitle', 'tutorialbooking'),
            array('class' => "main help")));
        // Session title.
        $mform->addElement('html', html_writer::tag('h4', $this->_customdata['title'], array('class' => "main help")));
        $mform->addElement('html', html_writer::tag('h3', $this->_customdata['stitle'], array('class' => "main help")));

        $mform->addElement('hidden', 'id', $this->_customdata['id']); // Session id.
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'tutorialid', $this->_customdata['tutorialid']);
        $mform->setType('tutorialid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'action', 'notifygroup');
        $mform->setType('action', PARAM_ALPHA);

        // Title.
        $mform->addElement('text', 'subject', get_string('subjecttitleprompt', 'tutorialbooking'), array('size' => 80));
        $mform->setDefault('subject', $this->_customdata['subject']);
        $mform->setType('subject', PARAM_TEXT);

        // Description.
        $mform->addElement('editor', 'message', get_string('messageprompt', 'tutorialbooking'));;
        $mform->setType('message', PARAM_RAW);

        // Buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'sendmessage', get_string('sendmessage', 'tutorialbooking'),
                array('id' => 'id_submitbutton'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('cancel', 'tutorialbooking'),
                array('id' => 'id_cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}
