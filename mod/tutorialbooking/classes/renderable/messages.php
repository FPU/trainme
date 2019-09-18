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

namespace mod_tutorialbooking\renderable;

defined('MOODLE_INTERNAL') || die;

/**
 * Used by the tutorial booking renderer to display messages.
 *
 * @package    mod_tutorialbooking
 * @copyright  2017 Nottingham University
 * @author     Neill Magill - neill.magill@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class messages {
    /** @var array An array of language strings. */
    public $strings;

    /** @var array An array of message objects. */
    public $messages = array();

    /**
     * Sets up the variables needed by the render.
     *
     * @param \moodle_recordset $messages
     */
    public function __construct(\moodle_recordset $messages) {
        $this->strings = array(
            'subject' => get_string('subjecttitleprompt', 'mod_tutorialbooking'),
            'date' => get_string('senttime', 'mod_tutorialbooking'),
            'sentby' => get_string('sentby', 'mod_tutorialbooking'),
            'sentto' => get_string('sentto', 'mod_tutorialbooking'),
        );

        foreach ($messages as $message) {
            $this->messages[] = array(
                'subject' => $message->subject,
                'date' => userdate($message->senttime),
                'sentby' => \mod_tutorialbooking_user::displayusernames(array($message->sentby)),
                'sentto' => \mod_tutorialbooking_user::displayusernames(unserialize($message->sentto)),
                'message' => $message->message,
            );
        }
    }
}
