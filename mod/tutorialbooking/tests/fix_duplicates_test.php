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

use mod_tutorialbooking\task\fix_duplicates;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests the tutorial booking mod_tutorialbooking\task\fix_duplicates class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_fix_duplicates_testcase extends advanced_testcase {
    /**
     * Tests that fix_duplicates::cron detects and removes duplicates in the same slot
     *
     * @covers mod_tutorialbooking\task\fix_duplicates::cron
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_duplicates_in_slot() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        // Create data for the test.
        $course = self::getDataGenerator()->create_course();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();
        $tutorialbooking1 = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking1);
        $signup1 = $generator->signup_user($tutorialbooking1, $slot1, $user1);
        $signup2 = $generator->signup_user($tutorialbooking1, $slot1, $user1);
        $signup3 = $generator->signup_user($tutorialbooking1, $slot1, $user1);
        $generator->signup_user($tutorialbooking1, $slot1, $user2);
        $slot2 = $generator->add_slot($tutorialbooking1);
        $generator->signup_user($tutorialbooking1, $slot2, $user3);
        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot3 = $generator->add_slot($tutorialbooking2);
        $signup4 = $generator->signup_user($tutorialbooking2, $slot3, $user1);
        $generator->signup_user($tutorialbooking2, $slot3, $user4);
        // Verify the setup.
        $this->assertEquals(7, $DB->count_records('tutorialbooking_signups'));
        // Start the test.
        $task = new fix_duplicates();
        ob_start();
        $task->execute();
        $output = ob_get_clean();
        // Verify the output and the state of Moodle.
        $this->assertContains("Fixed 1 instances of user duplication.", $output);
        $this->assertEquals(5, $DB->count_records('tutorialbooking_signups')); // 2 records should have been removed.
        $this->assertTrue($DB->record_exists('tutorialbooking_signups', array('id' => $signup1->id)));
        $this->assertTrue($DB->record_exists('tutorialbooking_signups', array('id' => $signup4->id)));
        $this->assertFalse($DB->record_exists('tutorialbooking_signups', array('id' => $signup2->id)));
        $this->assertFalse($DB->record_exists('tutorialbooking_signups', array('id' => $signup3->id)));
        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that fix_duplicates::cron detects and removes a user signed up in two slots on a tutorial.
     *
     * @covers mod_tutorialbooking\task\fix_duplicates::cron
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_duplicates_in_tutorial() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        // Create data for the test.
        $course = self::getDataGenerator()->create_course();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();
        $tutorialbooking1 = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking1);
        $signup1 = $generator->signup_user($tutorialbooking1, $slot1, $user1);
        $generator->signup_user($tutorialbooking1, $slot1, $user2);
        $slot2 = $generator->add_slot($tutorialbooking1);
        $signup2 = $generator->signup_user($tutorialbooking1, $slot2, $user1);
        $generator->signup_user($tutorialbooking1, $slot2, $user3);
        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot3 = $generator->add_slot($tutorialbooking2);
        $signup3 = $generator->signup_user($tutorialbooking2, $slot3, $user1);
        $generator->signup_user($tutorialbooking2, $slot3, $user4);
        // Verify the setup.
        $this->assertEquals(6, $DB->count_records('tutorialbooking_signups'));
        // Start the test.
        $task = new fix_duplicates();
        ob_start();
        $task->execute();
        $output = ob_get_clean();
        // Verify the output and the state of Moodle.
        $this->assertContains("Fixed 1 instances of user duplication.", $output);
        $this->assertEquals(5, $DB->count_records('tutorialbooking_signups')); // 1 record should have been removed.
        $this->assertTrue($DB->record_exists('tutorialbooking_signups', array('id' => $signup1->id)));
        $this->assertTrue($DB->record_exists('tutorialbooking_signups', array('id' => $signup3->id)));
        $this->assertFalse($DB->record_exists('tutorialbooking_signups', array('id' => $signup2->id)));
        $this->assertDebuggingNotCalled();
    }
}
