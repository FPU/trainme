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

namespace mod_tutorialbooking\exception;

defined('MOODLE_INTERNAL') || die;

/**
 * An exception to be used when something goes wrong with a tutorial booking session.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill - neill.magill@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_exception extends \moodle_exception {
    /**
     * Constructor
     * @param string $hint short description of problem
     * @param string $debuginfo detailed information how to fix problem
     */
    public function __construct($hint, $debuginfo=null) {
        parent::__construct('sessionerror', 'mod_tutorialbooking', '', $hint, $debuginfo);
    }
}
