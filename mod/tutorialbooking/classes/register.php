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

/**
 * Code for the selection boxes for staff to add users to a timeslot.
 *
 * @package    mod_tutorialbooking
 * @copyright  2013 Nottingham University
 * @author     Benjamin Ellis <benjamin.ellis@nottingham.ac.uk>
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_register {
    /** ORDER_NAME = 1 Order the register by name. */
    const ORDER_NAME = 1;

    /** ORDER_DATE = 2 Order the register by date of signup. */
    const ORDER_DATE = 2;

    /**
     * Gets all the signups for a session.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param int $sessionid The session id.
     * @param int $order The order of the results: self::ORDER_NAME or self::ORDER_DATE.
     * @return stdClass[]|false An array of users who have signed up, or false if none were found for the session.
     */
    public static function getsessionsignups($sessionid, $order = self::ORDER_NAME) {
        global $DB;

        $sql = "SELECT u.id, u.firstname, u.lastname "
                . "FROM {user} u, {tutorialbooking_signups} t "
                . "WHERE t.userid = u.id AND t.sessionid = ? ";

        if ($order === self::ORDER_DATE) {
            $sql .= 'ORDER BY t.signupdate, u.lastname, u.firstname';
        } else {
            $sql .= 'ORDER BY u.lastname, u.firstname';
        }
        return $DB->get_records_sql($sql, array($sessionid));
    }
}
