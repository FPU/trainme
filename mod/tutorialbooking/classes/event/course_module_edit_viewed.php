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
 * The mod_tutorialbooking course module viewed event.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tutorialbooking\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tutorialbooking course module viewed in edit mode event class.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_edit_viewed extends \core\event\course_module_viewed {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        parent::init();
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'tutorialbooking';
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $params = array(
            'tutorialid' => $this->objectid,
            'courseid' => $this->courseid,
        );
        return new \moodle_url("/mod/tutorialbooking/tutorialbooking_sessions.php", $params);
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/tutorialbooking/'));
        return array($this->courseid, 'tutorialbooking', 'editview', $logurl, 'Default View', $this->contextinstanceid);
    }
}
