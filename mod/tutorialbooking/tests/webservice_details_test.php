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
 * Tests the tutorial booking mod_tutorialbooking details web service.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   University of Nottingham, 2017
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class webservice_details_test extends advanced_testcase {
    /**
     * Tests that when a user is not signed up to a slot that the view is correct.
     *
     * @covers \mod_tutorialbooking\external\details::view
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_details_not_signed_up() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $student = $DB->get_record('role', array('shortname' => 'student'));
        $course = self::getDataGenerator()->create_course();
        $student1 = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        $student3 = self::getDataGenerator()->create_user();
        $student4 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student1->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($student2->id, $course->id, $student->id);
        // Setup a Tutorial booking.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $student1);
        $generator->signup_user($tutorialbooking, $slot1, $student2);
        $generator->signup_user($tutorialbooking, $slot2, $student3);
        // Run the test.
        self::setUser($student4);
        $result = \mod_tutorialbooking\external\details::view($tutorialbooking->id);
        $expected = array(
            'id' => $tutorialbooking->id,
            'title' => $tutorialbooking->name,
            'intro' => $tutorialbooking->intro,
            'introformat' => FORMAT_HTML,
            'privacy' => $tutorialbooking->privacy,
            'locked' => $tutorialbooking->locked,
            'signedup' => false,
            'slots' => array(
                array(
                    'id' => $slot1->id,
                    'title' => $slot1->description,
                    'titleformat' => FORMAT_HTML,
                    'summary' => $slot1->summary,
                    'summaryformat' => FORMAT_HTML,
                    'location' => $slot1->location,
                    'spaces' => $slot1->spaces,
                    'usedspaces' => 2,
                    'visible' => $slot1->visible,
                    'signedup' => false,
                ),
                array(
                    'id' => $slot2->id,
                    'title' => $slot2->description,
                    'titleformat' => FORMAT_HTML,
                    'summary' => $slot2->summary,
                    'summaryformat' => FORMAT_HTML,
                    'location' => $slot2->location,
                    'spaces' => $slot2->spaces,
                    'usedspaces' => 1,
                    'visible' => $slot2->visible,
                    'signedup' => false,
                ),
            ),
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that when a user is signed up to a slot that the view is correct.
     *
     * @covers \mod_tutorialbooking\external\details::view
     * @global moodle_database $DB The Moodle database connection object.
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_details_signed_up() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $student = $DB->get_record('role', array('shortname' => 'student'));
        $course = self::getDataGenerator()->create_course();
        $student1 = self::getDataGenerator()->create_user();
        $student2 = self::getDataGenerator()->create_user();
        $student3 = self::getDataGenerator()->create_user();
        $student4 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student1->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($student2->id, $course->id, $student->id);
        // Setup a Tutorial booking.
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $student1);
        $generator->signup_user($tutorialbooking, $slot1, $student2);
        $generator->signup_user($tutorialbooking, $slot2, $student3);
        $generator->signup_user($tutorialbooking, $slot2, $student4);
        // Run the test.
        self::setUser($student4);
        $result = \mod_tutorialbooking\external\details::view($tutorialbooking->id);
        $expected = array(
            'id' => $tutorialbooking->id,
            'title' => $tutorialbooking->name,
            'intro' => $tutorialbooking->intro,
            'introformat' => FORMAT_HTML,
            'privacy' => $tutorialbooking->privacy,
            'locked' => $tutorialbooking->locked,
            'signedup' => true,
            'slots' => array(
                array(
                    'id' => $slot1->id,
                    'title' => $slot1->description,
                    'titleformat' => FORMAT_HTML,
                    'summary' => $slot1->summary,
                    'summaryformat' => FORMAT_HTML,
                    'location' => $slot1->location,
                    'spaces' => $slot1->spaces,
                    'usedspaces' => 2,
                    'visible' => $slot1->visible,
                    'signedup' => false,
                ),
                array(
                    'id' => $slot2->id,
                    'title' => $slot2->description,
                    'titleformat' => FORMAT_HTML,
                    'summary' => $slot2->summary,
                    'summaryformat' => FORMAT_HTML,
                    'location' => $slot2->location,
                    'spaces' => $slot2->spaces,
                    'usedspaces' => 2,
                    'visible' => $slot2->visible,
                    'signedup' => true,
                ),
            ),
        );
        $this->assertEquals($expected, $result);
    }
}
