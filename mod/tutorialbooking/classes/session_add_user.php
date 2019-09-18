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

require_once(dirname(dirname(dirname(__DIR__ ))). '/user/selector/lib.php');

/**
 * Code for the selection boxes for staff to add users to a timeslot.
 *
 * @package    mod_tutorialbooking
 * @copyright  2013 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_session_add_user extends user_selector_base {
    /** @var int $tutorialid The id of the tutorial this selector is finding users for. */
    protected $tutorialid;

    /**
     * Constructor. Each subclass must have a constructor with this signature.
     *
     * @param string $name The control name/id for use in the HTML.
     * @param array $options Other options needed to construct this selector.
     * @throws Exception If an tutorialid is not passed in $option.
     */
    public function __construct($name, $options) {
        // We require the tutorialid and the sessionid to be sent.
        if (!isset($options['tutorialid'])) {
            throw new Exception('mod_tutorialbooking_session_add_user: invalid options');
        }
        $this->tutorialid = $options['tutorialid'];
        parent::__construct($name, $options);
    }

    /**
     * Search the database for users who are able to signup to the
     * tutorial booking activity this selector is for, but have yet to do so.
     *
     * @global moodle_database $DB The Moodle database object.
     * @param string $search Not used, but required as this is an over-ridden method from the parent class.
     * @return array
     */
    public function find_users($search) {
        global $DB;

        // Find ids of users enrolled on the tutorialbooking already.
        $alreadyenrolled = $DB->get_fieldset_select('tutorialbooking_signups', 'userid', 'tutorialid = :tutorialid',
                array('tutorialid' => $this->tutorialid));

        // Get the context for the tutorial booking.
        $cm = get_coursemodule_from_instance('tutorialbooking', $this->tutorialid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);

        // Get the users that can submit to the tutorialbooking, except those who are already signed up, order them by name.
        $userlist = get_users_by_capability($context, 'mod/tutorialbooking:submit', $this->required_fields_sql('u'),
                'u.lastname, u.firstname', '', '', '', $alreadyenrolled);

        return array(get_string('availabletoadd', 'tutorialbooking') => $userlist);
    }

    /**
     * Gets the options needed to recreate this user_selector.
     *
     * @return array
     */
    protected function get_options() {
        $options = parent::get_options();
        $options['tutorialid'] = $this->tutorialid;
        return $options;
    }

    /**
     * Output this user_selector as HTML.
     *
     * @param boolean $return If true, return the HTML as a string instead of outputting it.
     * @return string|void A string is returned if $return is true, otherwise nothing is returned by the method.
     */
    public function display($return = false) {
        // Get the list of requested users.
        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
            $search = '';
        }
        $groupedusers = $this->find_users($search);

        // Output the select.
        $name = $this->name;
        $selectparams = array('name' => $name, 'id' => $this->name, 'size' => $this->rows);
        if ($this->multiselect) {
            $selectparams['name'] .= '[]';
            $selectparams['multiple'] = 'multiple';
        }
        $output = html_writer::start_div('userselector', array('id' => $this->name . '_wrapper'));
        $output .= html_writer::start_tag('select', $selectparams);

        // Populate the select.
        $output .= $this->output_options($groupedusers, $search);

        // Output the search controls.
        $output .= html_writer::end_tag('select') . html_writer::end_div();

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}
