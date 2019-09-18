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
 * Library of interface functions and constants for module tutorialbooking
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle is placed here. Tutorialbooking specific functions
 * are in locallib.php.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function tutorialbooking_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
        case FEATURE_RATE:
        case FEATURE_PLAGIARISM:
            return false;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the tutorialbooking into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $tutorialbooking An object from the form in mod_form.php
 * @param mod_tutorialbooking_mod_form $mform
 * @return int The id of the newly inserted tutorialbooking record
 */
function tutorialbooking_add_instance(stdClass $tutorialbooking, mod_tutorialbooking_mod_form $mform = null) {
    global $DB;

    $tutorialbooking->timecreated = time();
    $tutorialbooking->id = $DB->insert_record('tutorialbooking', $tutorialbooking);
    \mod_tutorialbooking_tutorial::update_events($tutorialbooking);
    return $tutorialbooking->id;
}

/**
 * Updates an instance of the tutorialbooking in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $tutorialbooking An object from the form in mod_form.php
 * @param mod_tutorialbooking_mod_form $mform
 * @return boolean Success/Fail
 */
function tutorialbooking_update_instance(stdClass $tutorialbooking, mod_tutorialbooking_mod_form $mform = null) {
    global $DB;

    $tutorialbooking->timemodified = time();
    $tutorialbooking->id = $tutorialbooking->instance;
    $DB->update_record('tutorialbooking', $tutorialbooking);
    \mod_tutorialbooking_tutorial::update_events($tutorialbooking, $mform);
    return true;
}

/**
 * Removes an instance of the tutorialbooking from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function tutorialbooking_delete_instance($id) {
    global $DB;
    \mod_tutorialbooking_tutorial::delete_events($id);

    if (!$tutorialbooking = $DB->get_record('tutorialbooking', array('id' => $id))) {
        return false;
    }

    if ($DB->delete_records('tutorialbooking', array('id' => $tutorialbooking->id))) {
        // These will be orphaned records if they fail - so not too serious.
        $DB->delete_records('tutorialbooking_sessions', array('tutorialid' => $tutorialbooking->id));
        // Now the timeslots.
        $DB->delete_records('tutorialbooking_signups',  array('tutorialid' => $tutorialbooking->id));
    } else {
        return false;
    }
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function tutorialbooking_get_extra_capabilities() {
    return array('mod/tutorialbooking:submit');
}

/**
 * Obtains the automatic completion state for this tutorialbooking based on any conditions
 * in tutorialbooking settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not. (If no conditions, then return
 *   value depends on comparison type)
 */
function tutorialbooking_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get tutorialbooking details.
    if (!($tutorialbooking = $DB->get_record('tutorialbooking', array('id' => $cm->instance)))) {
        throw new Exception("Can't find tutorial {$cm->instance}");
    }

    $result = $type; // Default return value.

    if (!empty($tutorialbooking->completionsignedup)) { // Users must have signed up to a tutorial slot.
        $signedup = $DB->record_exists('tutorialbooking_signups', array('tutorialid' => $tutorialbooking->id, 'userid' => $userid));

        if ($type == COMPLETION_AND) {
            $result &= $signedup;
        } else {
            $result |= $signedup;
        }
    }

    return $result;
}

/**
 * Extends the settings navigation with the newmodule settings
 *
 * This function is called when the context for the page is a newmodule module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav
 * @param navigation_node $tutorialbookingnode
 */
function tutorialbooking_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $tutorialbookingnode = null) {
    global $PAGE;

    if ($tutorialbookingnode === null) {
        $tutorialbookingnode = new navigation_node(get_string('pluginname', 'mod_tutorialbooking'));
    }

    $canedit = $PAGE->user_allowed_editing();
    if ($canedit) {
        $tutorialbookingnode->add(get_string('linktomanagetext', 'tutorialbooking'),
                new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php',
                        array('tutorialid' => $PAGE->cm->instance,
                            'courseid' => $PAGE->course->id)));
        $tutorialbookingnode->add(get_string('pagecrumb', 'tutorialbooking'),
                new moodle_url('/mod/tutorialbooking/view.php',
                        array('id' => $PAGE->cm->id,
                            'redirect' => 0)));
    }
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function tutorialbooking_check_updates_since(cm_info $cm, $from, $filter = array()) {
    global $DB, $USER;
    $updates = new stdClass();
    if (!has_any_capability(array('mod/tutorialbooking:submit', 'mod/tutorialbooking:viewadminpage'), $cm->context)) {
        return $updates;
    }
    $updates = course_check_module_updates_since($cm, $from, array(), $filter);
    // Check for changes to the slots.
    $sessionselect = 'timemodified > :timemodified AND tutorialid = :tutorialid';
    $sessionparams = array('timemodified' => $from, 'tutorialid' => $cm->instance);
    $changedslots = $DB->get_fieldset_select('tutorialbooking_sessions', 'id', $sessionselect, $sessionparams);
    $updates->sessions = new stdClass();
    if (!empty($changedslots)) {
        $updates->sessions->updated = true;
        $updates->sessions->itemids = $changedslots;
    } else {
        $updates->sessions->updated = false;
    }
    // Check for a change to signups.
    $signupselect = 'signupdate > :signupdate AND tutorialid = :tutorialid';
    $signupparams = array('signupdate' => $from, 'tutorialid' => $cm->instance);
    if (!has_capability('mod/tutorialbooking:viewadminpage', $cm->context)) {
        // The user is not an teacher so should only be told if their own signup has updated.
        $signupselect .= ' AND userid = :user';
        $signupparams['user'] = $USER->id;
    }
    $changedsignups = $DB->get_fieldset_select('tutorialbooking_signups', 'id', $signupselect, $signupparams);
    $updates->signups = new stdClass();
    if (!empty($changedsignups)) {
        $updates->signups->updated = true;
        $updates->signups->itemids = $changedsignups;
    } else {
        $updates->signups->updated = false;
    }
    return $updates;
}

/**
 * Get an icon mapping for font-awesome
 *
 * @return array of mappings from the icon name to the font awesome name.
 */
function mod_tutorialbooking_get_fontawesome_icon_map() {
    $mapping = array(
        'mod_tutorialbooking:MoveUp' => 'fa-caret-square-o-up',
        'mod_tutorialbooking:MoveDown' => 'fa-caret-square-o-down',
        'mod_tutorialbooking:delete' => 'fa-minus-square',
    );
    return $mapping;
}

/**
 * Is the event visible?
 *
 * This is used to determine global visibility of an event in all places throughout Moodle.
 *
 * @param calendar_event $event
 * @return bool Returns true if the event is visible to the current user, false otherwise.
 */
function mod_tutorialbooking_core_calendar_is_event_visible(calendar_event $event) {
    $visibile = true;
    $cm = get_fast_modinfo($event->courseid)->instances['tutorialbooking'][$event->instance];
    $context = context_module::instance($cm->id);
    if ($event->eventtype = \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED) {
        $visibile = has_capability('mod/tutorialbooking:submit', $context);
    }
    return $visibile;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_tutorialbooking_core_calendar_provide_event_action(calendar_event $event, \core_calendar\action_factory $factory) {
    if ($event->eventtype = \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED) {
        $cm = get_fast_modinfo($event->courseid)->instances['tutorialbooking'][$event->instance];
        $name = get_string('signuprequired', 'mod_tutorialbooking');
        $url = new \moodle_url('/mod/tutorialbooking/view.php', [
            'id' => $cm->id,
            'redirect' => 0,
        ]);
        $itemcount = 1;
        $actionable = \mod_tutorialbooking_tutorial::get_signup($cm->instance) === false;
    } else {
        // Not an event we want to action.
        return null;
    }
    return $factory->create_instance(
        $name,
        $url,
        $itemcount,
        $actionable
    );
}

/**
 * Callback function that determines whether an action event should be showing its item count
 * based on the event type and the item count.
 *
 * @param calendar_event $event The calendar event.
 * @param int $itemcount The item count associated with the action event.
 * @return bool
 */
function mod_tutorialbooking_core_calendar_event_action_show_items_acount(calendar_event $event, $itemcount = 0) {
    return ($itemcount > 1);
}
