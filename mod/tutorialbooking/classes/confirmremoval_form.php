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
 * Email form for tutorial booking
 *
 * @package    mod_tutorialbooking
 * @copyright  2013 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_confirmremoval_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'tutorialid', $this->_customdata['tutorialid']);
        $mform->setType('tutorialid', PARAM_INT);
        $mform->addElement('hidden', 'user', $this->_customdata['userid']);
        $mform->setType('user', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'action', 'removesignupconfirm');
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('html', html_writer::tag('h2',
                get_string('confirmusersignupremoval', 'tutorialbooking'),
                array('class' => "main help")));
        $mform->addElement('html', html_writer::tag('h3',
                get_string('confirmmessage', 'tutorialbooking',
                        array('name' => $this->_customdata['username'],
                            'timeslot' => $this->_customdata['timeslotname']
                            )),
                array('class' => "main help")));

        $mform->addElement('header', 'messageheader', get_string('messagewillbesent', 'tutorialbooking'));
        $mform->addElement('editor', 'message', get_string('removalreason', 'tutorialbooking'));
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('reasonrequired', 'tutorialbooking'), 'required', null, 'client');

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'removesignupconfirm', get_string('remove', 'tutorialbooking'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('cancel', 'tutorialbooking'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}
