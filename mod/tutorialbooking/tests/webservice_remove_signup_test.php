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
 * Tests the tutorial booking mod_tutorialbooking remove signup web service.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   University of Nottingham, 2017
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class webservice_remove_signup_test extends advanced_testcase {
    /**
     * Tests that if a remove sign up for a user who has the correct
     * capabilities on the course is sent it is processed correctly.
     *
     * @covers \mod_tutorialbooking\external\removesignup::remove_signup
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_remove_signup() {
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
        // Do the test.
        $prarams = array(
            'tutorialid' => $tutorialbooking->id,
            'sessionid' => $slot1->id,
            'userid' => $student1->id,
        );
        $this->assertTrue($DB->record_exists('tutorialbooking_signups', $prarams));
        self::setUser($student1);
        $result = \mod_tutorialbooking\external\removesignup::remove_signup($slot1->id);
        $expectedresult = array(
            'success' => true,
            'error' => array(),
        );
        $this->assertEquals($expectedresult, $result);
        // Check that the correct database entry was removed.
        $this->assertFalse($DB->record_exists('tutorialbooking_signups', $prarams));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups'));
    }

    /**
     * Tests that if a remove is attempted for a user with no signup that the correct message is sent.
     *
     * @covers \mod_tutorialbooking\external\removesignup::remove_signup
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_no_signup() {
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
        // Do the test.
        self::setUser($student1);
        $result = \mod_tutorialbooking\external\removesignup::remove_signup($slot1->id);
        $expectedresult = array(
            'success' => false,
            'error' => array(
                'message' => get_string('nosignup', 'mod_tutorialbooking'),
            ),
        );
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Test that if a user does not have the capability to sign up they are not removed.
     *
     * @covers \mod_tutorialbooking\external\removesignup::remove_signup
     * @global moodle_database $DB
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_no_capability() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $student = $DB->get_record('role', array('shortname' => 'student'));
        $course = self::getDataGenerator()->create_course();
        $student1 = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student1->id, $course->id, $student->id); // Students.
        // Setup a Tutorial booking.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $student1);
        $generator->signup_user($tutorialbooking, $slot1, $student2);
        // Do the test.
        self::setUser($student2);
        $result = \mod_tutorialbooking\external\removesignup::remove_signup($slot1->id);
        $expectedresult = array(
            'success' => false,
            'error' => array(
                'message' => get_string('unauthorised', 'mod_tutorialbooking'),
            ),
        );
        $this->assertEquals($expectedresult, $result);
        // Check no additional signups have been created.
        $this->assertEquals(2, $DB->count_records('tutorialbooking_signups'));
    }

    /**
     * Test that if a slot is locked that the user cannot remove their sign up.
     *
     * @covers \mod_tutorialbooking\external\removesignup::remove_signup
     * @global moodle_database $DB
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_locked() {
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
        $tutorialbooking = $generator->create_instance(array('course' => $course->id, 'locked' => true));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 1));
        $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $student1);
        $generator->signup_user($tutorialbooking, $slot1, $student2);
        // Do the test.
        self::setUser($student2);
        $result = \mod_tutorialbooking\external\removesignup::remove_signup($slot1->id);
        $expectedresult = array(
            'success' => false,
            'error' => array(
                'message' => get_string('lockederror', 'mod_tutorialbooking'),
            ),
        );
        $this->assertEquals($expectedresult, $result);
        // Check no additional signups have been created.
        $this->assertEquals(2, $DB->count_records('tutorialbooking_signups'));
    }
}
