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
 * Tests the tutorial booking mod_tutorialbooking_register class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_register_testcase extends advanced_testcase {
    /**
     * Tests that mod_tutorialbooking_register::generate_formdata generates a file as expected.
     *
     * @covers mod_tutorialbooking_register::getsessionsignups
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_getsessionsignups() {
        $this->resetAfterTest(true);
        $this->setAdminUser(); // The admin user has an id of 2.
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $user0 = self::getDataGenerator()->create_user(array('firstname' => 'Andrew', 'lastname' => 'Ali'));
        $user1 = self::getDataGenerator()->create_user(array('firstname' => 'Ben', 'lastname' => 'Ali'));
        $user2 = self::getDataGenerator()->create_user(array('firstname' => 'Andrew', 'lastname' => 'Bierhoff'));
        $user3 = self::getDataGenerator()->create_user(array('firstname' => 'Joe', 'lastname' => 'Dolefield'));
        $user4 = self::getDataGenerator()->create_user(array('firstname' => 'Garry', 'lastname' => 'Haggerty'));
        $user5 = self::getDataGenerator()->create_user(array('firstname' => 'Andrew', 'lastname' => 'Xue'));

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot = $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $generator->signup_user($tutorialbooking, $slot, $user5);
        sleep(1); // Ensure a new timestamp.
        $generator->signup_user($tutorialbooking, $slot, $user3);
        sleep(1); // Ensure a new timestamp.
        $generator->signup_user($tutorialbooking, $slot, $user2);
        sleep(1); // Ensure a new timestamp.
        $generator->signup_user($tutorialbooking, $slot, $user4);
        sleep(1); // Ensure a new timestamp.
        $generator->signup_user($tutorialbooking, $slot, $user0);
        sleep(1); // Ensure a new timestamp.
        $generator->signup_user($tutorialbooking, $slot2, $user1);

        // Check users signed up for a slot are returned correctly in name order,
        // and that users from other slots are not returned as well.
        $nameresults = mod_tutorialbooking_register::getsessionsignups($slot->id, mod_tutorialbooking_register::ORDER_NAME);
        $this->assertCount(5, $nameresults);
        $nameresult0 = array_shift($nameresults);
        $this->record_for_user($user0, $nameresult0);
        $nameresult1 = array_shift($nameresults);
        $this->record_for_user($user2, $nameresult1);
        $nameresult2 = array_shift($nameresults);
        $this->record_for_user($user3, $nameresult2);
        $nameresult3 = array_shift($nameresults);
        $this->record_for_user($user4, $nameresult3);
        $nameresult4 = array_shift($nameresults);
        $this->record_for_user($user5, $nameresult4);

        // Check users signed up for a slot are returned correctly in date order,
        // and that users from other slots are not returned as well.
        $dateresults = mod_tutorialbooking_register::getsessionsignups($slot->id, mod_tutorialbooking_register::ORDER_DATE);
        $this->assertCount(5, $dateresults);
        $dateresult0 = array_shift($dateresults);
        $this->record_for_user($user5, $dateresult0);
        $dateresult1 = array_shift($dateresults);
        $this->record_for_user($user3, $dateresult1);
        $dateresult2 = array_shift($dateresults);
        $this->record_for_user($user2, $dateresult2);
        $dateresult3 = array_shift($dateresults);
        $this->record_for_user($user4, $dateresult3);
        $dateresult4 = array_shift($dateresults);
        $this->record_for_user($user0, $dateresult4);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Checks that a single record returned by the mod_tutorialbooking_register::getsessionsignups
     * method matches the given user.
     *
     * @param stdClass $user The user the record should match.
     * @param stdClass $record A record returned by the mod_tutorialbooking_register::getsessionsignups method.
     */
    protected function record_for_user($user, $record) {
        $this->assertAttributeEquals($user->id, 'id', $record);
        $this->assertAttributeEquals($user->firstname, 'firstname', $record);
        $this->assertAttributeEquals($user->lastname, 'lastname', $record);
    }
}
