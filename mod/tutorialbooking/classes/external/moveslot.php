<?php
// This file is part of the tutorial booking plugin
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
//

namespace mod_tutorialbooking\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Defines the web service endpoint for moving a sign up in a Tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2018 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moveslot extends \external_api {
    /**
     * Processes reordering of signup slots in a tutorial booking activity.
     *
     * @param int $tutorialid
     * @param int $slotid
     * @param int $targetid
     * @return array
     * @throws moodle_exception
     */
    public static function move($tutorialid, $slotid, $targetid) {
        $paramarray = array(
            'tutorial' => $tutorialid,
            'slot' => $slotid,
            'target' => $targetid,
        );
        $params = self::validate_parameters(self::move_parameters(), $paramarray);
        list($course, $cm) = get_course_and_cm_from_instance($params['tutorial'], 'tutorialbooking');
        $context = \context_module::instance($cm->id);
        // Check the user has the capability to edit the tutorial booking.
        require_capability('mod/tutorialbooking:viewadminpage', $context);
        // Check that the slots are valid.
        $slots = \mod_tutorialbooking_session::get_slot_records(array($params['slot'], $params['target']));
        if (empty($slots[$params['slot']]) || empty($slots[$params['target']])) {
            // The slots do not all exist.
            throw new \moodle_exception('ajax_slots_not_exist', 'mod_tutorialbooking');
        }
        $slotintutorial = $slots[$params['slot']]->tutorialid == $params['tutorial'];
        $targetintutorial = $slots[$params['target']]->tutorialid == $params['tutorial'];
        if (!$slotintutorial || !$targetintutorial) {
            // The slots are not both in the tutorial booking.
            throw new \moodle_exception('ajax_invalid_slots', 'mod_tutorialbooking');
        }
        // Now do the move.
        $originalposition = $slots[$params['slot']]->sequence;
        $targetposition = $slots[$params['target']]->sequence;
        $direction = ($originalposition > $targetposition) ? 'before' : 'after';
        $response = array(
            'success' => \mod_tutorialbooking_session::move_sequence($tutorialid, $originalposition, $targetposition),
            'where' => $direction,
        );
        return $response;
    }

    /**
     * Defines the inputs for the web service method.
     *
     * @return \external_function_parameters
     */
    public static function move_parameters() {
        return new \external_function_parameters(array(
            'tutorial' => new \external_value(PARAM_INT, 'The id of a Tutorial booking activity', VALUE_REQUIRED),
            'slot' => new \external_value(PARAM_INT, 'The id of a Tutorial booking slot to move', VALUE_REQUIRED),
            'target' => new \external_value(PARAM_INT, 'The id of the Tutorial booking slot that the slot is being dragged onto', VALUE_REQUIRED),
        ));
    }

    /**
     * Defines the output of the web service.
     *
     * @return \external_function_parameters
     */
    public static function move_returns() {
        return new \external_function_parameters(array(
            'success' => new \external_value(PARAM_BOOL, 'Returns is the removal was a success', VALUE_REQUIRED),
            'where' => new \external_value(PARAM_ALPHA, 'If the slot was moved before or after the the target', VALUE_REQUIRED),
        ));
    }
}
