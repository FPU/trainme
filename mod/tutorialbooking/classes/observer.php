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

use mod_tutorialbooking\event\signup_capability_removed;

/**
 * Class for taking action when events the activity is observing are fired.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_observer {
    /**
     * Checks to see if a user needs to be deleted from any tutorial booking activities when they
     * have a role removed from them on a course.
     *
     * @param \core\event\role_unassigned $event
     * @return void
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        self::unenrol($event);
    }

    /**
     * Removes users from Tutorial booking activities on a course when they are completely removed from a course.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return void
     */
    public static function user_unenrolled(\core\event\user_enrolment_deleted $event) {
        self::unenrol($event);
    }

    /**
     * Does the actual removal of the user data for the observers.
     *
     * @global moodle_database $DB The moodle database connection object.
     * @param \core\event\base $event
     * @return void
     */
    protected static function unenrol(\core\event\base $event) {
        global $DB;

        $context = $event->get_context();

        if ($context->contextlevel == CONTEXT_COURSE) { // The context is for a course.
            // We now need to check if the user still has the ability to add themselves to tutorialbooking sessions.
            if (!has_capability('mod/tutorialbooking:submit', $context, $event->relateduserid)) { // Does not have the capability.
                $deletedsignups = $DB->get_records('tutorialbooking_signups', array('userid' => $event->relateduserid,
                    'courseid' => $context->instanceid));
                if (empty($deletedsignups)) {
                    // No record to delete, so we do not wish to fire the event.
                    return;
                }
                $DB->delete_records('tutorialbooking_signups', array('userid' => $event->relateduserid,
                    'courseid' => $context->instanceid));
                $eventdata = array(
                    'context' => $context,
                    'objectid' => $context->instanceid,
                    'userid' => $event->userid,
                    'relateduserid' => $event->relateduserid,
                );
                $event = signup_capability_removed::create($eventdata);
                foreach ($deletedsignups as $deleted) {
                    $event->add_record_snapshot('tutorialbooking_signups', $deleted);
                }
                $event->trigger();
            }
        }
    }
}