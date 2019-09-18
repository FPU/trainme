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
 * Tests the tutorial booking mod_tutorialbooking capabilities web service.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   University of Nottingham, 2017
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class webservice_capabilities_test extends advanced_testcase {
    /**
     * Tests a user signed up as a student.
     *
     * @covers \mod_tutorialbooking\external\capabilities::get
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_capabilities_student() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        // Set up the user and course.
        $student = $DB->get_record('role', array('shortname' => 'student'));
        $course = self::getDataGenerator()->create_course();
        $student1 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student1->id, $course->id, $student->id);
        // Setup the Tutorial bookign activity.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        // Run the test.
        self::setUser($student1);
        $result = \mod_tutorialbooking\external\capabilities::get($tutorialbooking->id);
        $expected = array(
            'submit' => true,
            'removeuser' => false,
            'adduser' => false,
            'oversubscribe' => false,
            'viewadminpage' => false,
            'editsignuplist' => false,
            'export' => false,
            'message' => false,
            'printregisters' => false,
            'viewallmessages' => false,
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests a user that is not on the course.
     *
     * @covers \mod_tutorialbooking\external\capabilities::get
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_capabilities_not_on_course() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        // Set up the user and course.
        $student = $DB->get_record('role', array('shortname' => 'student'));
        $course = self::getDataGenerator()->create_course();
        $student1 = self::getDataGenerator()->create_user();
        // Setup the Tutorial bookign activity.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        // Run the test.
        self::setUser($student1);
        $result = \mod_tutorialbooking\external\capabilities::get($tutorialbooking->id);
        $expected = array(
            'submit' => false,
            'removeuser' => false,
            'adduser' => false,
            'oversubscribe' => false,
            'viewadminpage' => false,
            'editsignuplist' => false,
            'export' => false,
            'message' => false,
            'printregisters' => false,
            'viewallmessages' => false,
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests a teacher on the course.
     *
     * @covers \mod_tutorialbooking\external\capabilities::get
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_capabilities_teacher() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        // Set up the user and course.
        $teacher = $DB->get_record('role', array('shortname' => 'teacher'));
        $course = self::getDataGenerator()->create_course();
        $teacher1 = self::getDataGenerator()->create_user();
        // Setup the Tutorial bookign activity.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        self::getDataGenerator()->enrol_user($teacher1->id, $course->id, $teacher->id);
        // Run the test.
        self::setUser($teacher1);
        $result = \mod_tutorialbooking\external\capabilities::get($tutorialbooking->id);
        $expected = array(
            'submit' => false,
            'removeuser' => false,
            'adduser' => false,
            'oversubscribe' => false,
            'viewadminpage' => true,
            'editsignuplist' => false,
            'export' => false,
            'message' => true,
            'printregisters' => true,
            'viewallmessages' => false,
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests an editing teacher on the course.
     *
     * @covers \mod_tutorialbooking\external\capabilities::get
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_capabilities_editingteacher() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        // Set up the user and course.
        $teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $course = self::getDataGenerator()->create_course();
        $teacher1 = self::getDataGenerator()->create_user();
        // Setup the Tutorial bookign activity.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        self::getDataGenerator()->enrol_user($teacher1->id, $course->id, $teacher->id);
        // Run the test.
        self::setUser($teacher1);
        $result = \mod_tutorialbooking\external\capabilities::get($tutorialbooking->id);
        $expected = array(
            'submit' => false,
            'removeuser' => true,
            'adduser' => true,
            'oversubscribe' => true,
            'viewadminpage' => true,
            'editsignuplist' => true,
            'export' => true,
            'message' => true,
            'printregisters' => true,
            'viewallmessages' => true,
        );
        $this->assertEquals($expected, $result);
    }
}
