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

defined('MOODLE_INTERNAL') || die();

/**
 * Tests the tutorial booking mod_tutorialbooking view web service.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   University of Nottingham, 2017
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class webservice_view_test extends advanced_testcase {
    /**
     * Test that there are no error s when running the webservice.
     *
     * @covers \mod_tutorialbooking\external\view::view
     * @global moodle_database $DB The Moodle database connection object.
     */
    public function test_view() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $student = $DB->get_record('role', array('shortname' => 'student'));
        $course = self::getDataGenerator()->create_course();
        $student1 = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student1->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($student2->id, $course->id, $student->id);
        // Setup a Tutorial booking.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $student1);
        $generator->signup_user($tutorialbooking, $slot1, $student2);

        self::setUser($student1);
        $result = \mod_tutorialbooking\external\view::view($tutorialbooking->id);
        $expectedresult = array(
            'status' => true,
            'warnings' => array(),
        );
        $this->assertEquals($expectedresult, $result);
    }
}
