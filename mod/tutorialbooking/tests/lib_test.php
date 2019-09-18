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

require_once(dirname(__DIR__) . '/lib.php');

/**
 * Tests the tutorial booking mod_tutorialbooking library methods.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2016
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_lib_testcase extends advanced_testcase {
    /**
     * Tests the tutorial_check_updates_since() method as a student.
     *
     * @covers tutorial_check_updates_since()
     */
    public function test_check_updates_since_student() {
        $this->resetAfterTest(true);
        $tutorialgenerator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        // Several dates in an ascending order.
        $date1 = new DateTime('2016-10-03T13:00:00+0000');
        $date2 = new DateTime('2016-10-03T13:54:01+0000');
        $date3 = new DateTime('2016-10-03T15:56:42+0000');
        $date4 = new DateTime('2016-10-06T09:12:30+0000');
        $date5 = new DateTime('2016-10-07T13:13:13+0000');
        $date6 = new DateTime('2016-10-08T14:42:42+0000');
        $date7 = new DateTime('2016-11-11T11:11:11+0000');

        // A course and some users.
        $course = self::getDataGenerator()->create_course();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, 'teacher');

        // Create a tutorial booking.
        $paramas1 = array(
            'course' => $course->id,
            'timecreated' => $date1->getTimestamp(),
            'timemodified' => $date2->getTimestamp(),
        );
        $tutorialbooking1 = $tutorialgenerator->create_instance($paramas1);
        $slot1params = array('timecreated' => $date1->getTimestamp(), 'timemodified' => $date2->getTimestamp());
        $slot1 = $tutorialgenerator->add_slot($tutorialbooking1, $slot1params);
        $slot2params = array('timecreated' => $date3->getTimestamp(), 'timemodified' => $date5->getTimestamp());
        $slot2 = $tutorialgenerator->add_slot($tutorialbooking1, $slot2params);
        $signup1 = $tutorialgenerator->signup_user($tutorialbooking1, $slot1, $user1, $date6->getTimestamp());
        $signup2 = $tutorialgenerator->signup_user($tutorialbooking1, $slot2, $user2, $date5->getTimestamp());

        // Create a second tutorial booking.
        $paramas2 = array(
            'course' => $course->id,
            'timecreated' => $date2->getTimestamp(),
            'timemodified' => $date3->getTimestamp(),
        );
        $tutorialbooking2 = $tutorialgenerator->create_instance($paramas2);
        $slot3params = array('timecreated' => $date3->getTimestamp(), 'timemodified' => $date4->getTimestamp());
        $slot3 = $tutorialgenerator->add_slot($tutorialbooking2, $slot3params);
        $tutorialgenerator->signup_user($tutorialbooking2, $slot3, $user1, $date6->getTimestamp());

        $cm1 = $this->get_cm($course, $user1, $tutorialbooking1);

        // A student should only see their own details for the given tutorial booking.
        $this->setUser($user1);
        $result1 = tutorialbooking_check_updates_since($cm1, $date1->getTimestamp());
        $this->assertInstanceOf('stdClass', $result1);
        $this->assertNotEmpty($result1);
        $this->assertObjectHasAttribute('sessions', $result1);
        $this->assertNotEmpty($result1->sessions);
        $this->assertTrue($result1->sessions->updated);
        $this->assertCount(2, $result1->sessions->itemids);
        $this->assertContains($slot1->id, $result1->sessions->itemids);
        $this->assertContains($slot2->id, $result1->sessions->itemids);
        $this->assertObjectHasAttribute('signups', $result1);
        $this->assertNotEmpty($result1->signups);
        $this->assertTrue($result1->signups->updated);
        $this->assertCount(1, $result1->signups->itemids);
        $this->assertContains($signup1->id, $result1->signups->itemids);

        $result2 = tutorialbooking_check_updates_since($cm1, $date5->getTimestamp());
        $this->assertInstanceOf('stdClass', $result2);
        $this->assertNotEmpty($result2);
        $this->assertObjectHasAttribute('sessions', $result2);
        $this->assertNotEmpty($result2->sessions);
        $this->assertFalse($result2->sessions->updated);
        $this->assertObjectNotHasAttribute('itemids', $result2->sessions);
        $this->assertObjectHasAttribute('signups', $result2);
        $this->assertNotEmpty($result2->signups);
        $this->assertTrue($result2->signups->updated);
        $this->assertCount(1, $result2->signups->itemids);
        $this->assertContains($signup1->id, $result2->signups->itemids);

        $result3 = tutorialbooking_check_updates_since($cm1, $date7->getTimestamp());
        $this->assertInstanceOf('stdClass', $result3);
        $this->assertNotEmpty($result2);
        $this->assertObjectHasAttribute('sessions', $result3);
        $this->assertNotEmpty($result3->sessions);
        $this->assertFalse($result3->sessions->updated);
        $this->assertObjectNotHasAttribute('itemids', $result3->sessions);
        $this->assertObjectHasAttribute('signups', $result3);
        $this->assertNotEmpty($result3->signups);
        $this->assertFalse($result3->signups->updated);
        $this->assertObjectNotHasAttribute('itemids', $result3->signups);

        // Teachers should see all the the signups in an activity.
        $cm2 = $this->get_cm($course, $user3, $tutorialbooking1);
        $this->setUser($user3);
        $result4 = tutorialbooking_check_updates_since($cm2, $date1->getTimestamp());
        $this->assertInstanceOf('stdClass', $result4);
        $this->assertNotEmpty($result4);
        $this->assertObjectHasAttribute('sessions', $result4);
        $this->assertNotEmpty($result4->sessions);
        $this->assertTrue($result4->sessions->updated);
        $this->assertCount(2, $result4->sessions->itemids);
        $this->assertContains($slot1->id, $result4->sessions->itemids);
        $this->assertContains($slot2->id, $result4->sessions->itemids);
        $this->assertObjectHasAttribute('signups', $result4);
        $this->assertNotEmpty($result4->signups);
        $this->assertTrue($result4->signups->updated);
        $this->assertCount(2, $result4->signups->itemids);
        $this->assertContains($signup1->id, $result4->signups->itemids);
        $this->assertContains($signup2->id, $result4->signups->itemids);

        $result5 = tutorialbooking_check_updates_since($cm2, $date5->getTimestamp());
        $this->assertInstanceOf('stdClass', $result5);
        $this->assertNotEmpty($result5);
        $this->assertObjectHasAttribute('sessions', $result5);
        $this->assertNotEmpty($result5->sessions);
        $this->assertFalse($result5->sessions->updated);
        $this->assertObjectNotHasAttribute('itemids', $result5->sessions);
        $this->assertObjectHasAttribute('signups', $result5);
        $this->assertNotEmpty($result5->signups);
        $this->assertTrue($result5->signups->updated);
        $this->assertCount(1, $result5->signups->itemids);
        $this->assertContains($signup1->id, $result5->signups->itemids);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Get the course module information for a tutorial booking.
     *
     * @param stdClass $course
     * @param stdClass $user
     * @param stdClass $tutorialbooking
     * @return cm_info
     */
    protected function get_cm($course, $user, $tutorialbooking) {
        $modinfo = get_fast_modinfo($course, $user->id);
        $instances = $modinfo->get_instances_of('tutorialbooking');
        return $instances[$tutorialbooking->id];
    }
}
