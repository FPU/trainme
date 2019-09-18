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
 * Tests the tutorial booking mod_tutorialbooking move slot web service.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   University of Nottingham, 2018
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class webservice_moveslot_test extends advanced_testcase {
    /**
     * Tests that the correct response is generated when the slow is moved 'up'
     *
     * @global moodle_database $DB
     */
    public function test_moveslot_up() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $slot3 = $generator->add_slot($tutorialbooking, array('spaces' => 5));
        $slot4 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        self::setAdminUser();
        $result = \mod_tutorialbooking\external\moveslot::move($tutorialbooking->id, $slot2->id, $slot4->id);
        $expectedresult = array(
            'success' => true,
            'where' => 'after',
        );
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests that the correct response is generated when moving slots 'down'.
     *
     * @global moodle_database $DB
     */
    public function test_moveslot_down() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $slot3 = $generator->add_slot($tutorialbooking, array('spaces' => 5));
        $slot4 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        self::setAdminUser();
        $result = \mod_tutorialbooking\external\moveslot::move($tutorialbooking->id, $slot3->id, $slot1->id);
        $expectedresult = array(
            'success' => true,
            'where' => 'before',
        );
        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Tests that an exception is raised if the user does not have the correct capability.
     *
     * @global moodle_database $DB
     */
    public function test_no_capability() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        self::setGuestUser();
        $this->expectException('required_capability_exception');
        \mod_tutorialbooking\external\moveslot::move($tutorialbooking->id, $slot1->id, $slot2->id);
    }

    /**
     * Tests that an exception is generated when the slots do not exist.
     *
     * @global moodle_database $DB
     */
    public function test_invalid_slot() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        self::setAdminUser();
        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string('ajax_slots_not_exist', 'mod_tutorialbooking'));
        $result = \mod_tutorialbooking\external\moveslot::move($tutorialbooking->id, 5, 6);
    }

    /**
     * Tests that an exception is raised when the slots are not part of the correct tutorial booking.
     *
     * @global moodle_database $DB
     */
    public function test_slot_in_different_tutorial() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $course = self::getDataGenerator()->create_course();
        $tutorialbooking1 = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking1, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking1, array('spaces' => 3));
        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $generator->add_slot($tutorialbooking2, array('spaces' => 3));
        $generator->add_slot($tutorialbooking2, array('spaces' => 3));
        self::setAdminUser();
        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string('ajax_invalid_slots', 'mod_tutorialbooking'));
        $result = \mod_tutorialbooking\external\moveslot::move($tutorialbooking2->id, $slot1->id, $slot2->id);
    }
}
