<?php
// This file is part of Moodle - http://moodle.org/
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
 * The mod_tutorialbooking event for a session being created.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tutorialbooking event for a session being created by a teacher.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int tutorialid: id of the tutorial booking activity the session is part of.
 * }
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_message extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'tutorialbooking_sessions';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has messaged all users in session with id '$this->objectid' in the tutorial "
                . "booking activity with id '{$this->other['tutorialid']}' in the course with id '$this->courseid'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventsessionmessage', 'mod_tutorialbooking');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        $params = array(
            'tutorialid' => $this->other['tutorialid'],
            'courseid' => $this->courseid,
        );
        return new \moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', $params);
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        // The legacy log table expects a relative path to /mod/tutorialbooking/.
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/tutorialbooking/'));
        return array($this->courseid, 'tutorialbooking', 'notify group', $logurl, '', $this->contextinstanceid);
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
        if (!isset($this->other['tutorialid'])) {
            throw new \coding_exception('The \'tutorialid\' value must be set in other.');
        }
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
