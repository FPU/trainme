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
 * The mod_tutorialbooking event for a signup being removed when the user
 * loses the capability to signup.
 *
 * @package    mod_tutorialbooking
 * @copyright  2015 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tutorialbooking event for a signup being removed when the user loses the capability to signup.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int tutorialid: id of the tutorial booking activity the session is part of.
 *      - string tutorialname: the name of the tutorial booking activity.
 *      - int sessionid: id of the tutorialbooking_session the user was removed from.
 * }
 *
 * @package    mod_tutorialbooking
 * @copyright  2015 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup_capability_removed extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'course';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->relateduserid' has been removed from their signups to all of the tutorial booking "
                . "activities in the course with id '$this->courseid' because user '$this->userid' has removed the "
                . "mod/tutorialbooking:submit capability from them.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventsignupcapabilityremoved', 'mod_tutorialbooking');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/tutorialbooking/index.php',
            array('id' => $this->contextinstanceid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        // The legacy log table expects a relative path to /mod/tutorialbooking/.
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/tutorialbooking/'));
        $message = "mod/tutorialbooking:submit capability removed from user, signups in course removed.";
        return array($this->courseid, 'tutorialbooking', 'capability removed', $logurl, $message, $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->objecttable)) {
            throw new \coding_exception('The \'objecttable\' value must be set in data.');
        }
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' value must be set in data.');
        }
        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }
}
