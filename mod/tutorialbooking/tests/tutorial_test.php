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
 * Tests the tutorial booking mod_tutorialbooking_tutorial class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_tutorial_testcase extends advanced_testcase {
    /**
     * Tests that mod_tutorialbooking_tutorial::getstatsfortutorial works correctly.
     *
     * @covers mod_tutorialbooking_tutorial::getstatsfortutorial
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_getstatsfortutorial() {
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();
        $user5 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 42));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 9));
        $slot3 = $generator->add_slot($tutorialbooking, array('spaces' => 500));
        $generator->add_slot($tutorialbooking, array('spaces' => 69));
        $generator->signup_user($tutorialbooking, $slot1, $user0);
        $generator->signup_user($tutorialbooking, $slot1, $user1);
        $generator->signup_user($tutorialbooking, $slot2, $user2);
        $generator->signup_user($tutorialbooking, $slot3, $user3);
        $generator->signup_user($tutorialbooking, $slot2, $user4);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2, array('spaces' => 5));
        $slot6 = $generator->add_slot($tutorialbooking2, array('spaces' => 2));
        $slot7 = $generator->add_slot($tutorialbooking2, array('spaces' => 3));
        $generator->signup_user($tutorialbooking2, $slot5, $user0);
        $generator->signup_user($tutorialbooking2, $slot6, $user1);
        $generator->signup_user($tutorialbooking2, $slot6, $user2);
        $generator->signup_user($tutorialbooking2, $slot7, $user3);
        $generator->signup_user($tutorialbooking2, $slot7, $user4);
        $generator->signup_user($tutorialbooking2, $slot7, $user5);

        $stats = mod_tutorialbooking_tutorial::getstatsfortutorial($tutorialbooking->id);
        $this->assertAttributeEquals(5, 'signedup', $stats);
        $this->assertAttributeEquals(620, 'places', $stats);

        $stats2 = mod_tutorialbooking_tutorial::getstatsfortutorial($tutorialbooking2->id);
        $this->assertAttributeEquals(6, 'signedup', $stats2);
        $this->assertAttributeEquals(10, 'places', $stats2);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_tutorial::togglelock works correctly.
     *
     * @covers mod_tutorialbooking_tutorial::togglelock
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_togglelock() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id, 'locked' => 0));
        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));

        $this->assertEquals(0, $DB->get_field('tutorialbooking', 'locked', array('id' => $tutorialbooking->id)));

        $this->assertTrue(mod_tutorialbooking_tutorial::togglelock($tutorialbooking->id, true));
        $this->assertEquals(1, $DB->get_field('tutorialbooking', 'locked', array('id' => $tutorialbooking->id)));
        $this->verify_tutorial_record_unchanged($tutorialbooking2);

        $this->assertTrue(mod_tutorialbooking_tutorial::togglelock($tutorialbooking->id, false));
        $this->assertEquals(0, $DB->get_field('tutorialbooking', 'locked', array('id' => $tutorialbooking->id)));
        $this->verify_tutorial_record_unchanged($tutorialbooking2);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_tutorial::gettutorialsessions works correctly.
     *
     * @covers mod_tutorialbooking_tutorial::gettutorialsessions
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_gettutorialsessions() {
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('sequence' => 1));
        $slot2 = $generator->add_slot($tutorialbooking, array('sequence' => 3));
        $slot3 = $generator->add_slot($tutorialbooking, array('sequence' => 2));
        $slot4 = $generator->add_slot($tutorialbooking, array('sequence' => 4));

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2, array('spaces' => 5));
        $slot6 = $generator->add_slot($tutorialbooking2, array('spaces' => 2));
        $slot7 = $generator->add_slot($tutorialbooking2, array('spaces' => 3));

        $sessions = mod_tutorialbooking_tutorial::gettutorialsessions($tutorialbooking->id);
        $this->assertCount(4, $sessions);
        $this->assertArrayHasKey($slot1->id, $sessions);
        $this->assertArrayHasKey($slot2->id, $sessions);
        $this->assertArrayHasKey($slot3->id, $sessions);
        $this->assertArrayHasKey($slot4->id, $sessions);
        $this->assertEquals($slot1, $sessions[$slot1->id]);
        $this->assertEquals($slot2, $sessions[$slot2->id]);
        $this->assertEquals($slot3, $sessions[$slot3->id]);
        $this->assertEquals($slot4, $sessions[$slot4->id]);

        $sessions2 = mod_tutorialbooking_tutorial::gettutorialsessions($tutorialbooking2->id);
        $this->assertCount(3, $sessions2);
        $this->assertArrayHasKey($slot5->id, $sessions2);
        $this->assertArrayHasKey($slot6->id, $sessions2);
        $this->assertArrayHasKey($slot7->id, $sessions2);
        $this->assertEquals($slot5, $sessions2[$slot5->id]);
        $this->assertEquals($slot6, $sessions2[$slot6->id]);
        $this->assertEquals($slot7, $sessions2[$slot7->id]);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_tutorial::gettutorialsignups works correctly.
     *
     * @covers mod_tutorialbooking_tutorial::gettutorialsignups
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_gettutorialsignups() {
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        // Setup data for the test.
        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();
        $user5 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 42));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 9));
        $slot3 = $generator->add_slot($tutorialbooking, array('spaces' => 500));
        $generator->add_slot($tutorialbooking, array('spaces' => 69));
        $generator->signup_user($tutorialbooking, $slot1, $user0);
        $generator->signup_user($tutorialbooking, $slot1, $user1);
        $generator->signup_user($tutorialbooking, $slot2, $user2);
        $generator->signup_user($tutorialbooking, $slot3, $user3);
        $generator->signup_user($tutorialbooking, $slot2, $user4);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2, array('spaces' => 5));
        $slot6 = $generator->add_slot($tutorialbooking2, array('spaces' => 2));
        $slot7 = $generator->add_slot($tutorialbooking2, array('spaces' => 3));
        $generator->signup_user($tutorialbooking2, $slot5, $user0);
        $generator->signup_user($tutorialbooking2, $slot6, $user1);
        $generator->signup_user($tutorialbooking2, $slot6, $user2);
        $generator->signup_user($tutorialbooking2, $slot7, $user3);
        $generator->signup_user($tutorialbooking2, $slot7, $user4);
        $generator->signup_user($tutorialbooking2, $slot7, $user5);

        // Start the test.
        $stats = mod_tutorialbooking_tutorial::gettutorialsignups($tutorialbooking->id);
        $this->assertCount(3, $stats); // There are no signups in slot4 so it will not be returned.
        $this->assertArrayHasKey($slot1->id, $stats);
        $this->assertArrayHasKey($slot2->id, $stats);
        $this->assertArrayHasKey($slot3->id, $stats);
        $this->assertCount(3, $stats[$slot1->id]);
        $this->assertCount(3, $stats[$slot2->id]);
        $this->assertCount(3, $stats[$slot3->id]);
        $this->assertCount(2, $stats[$slot1->id]['signedup']);
        $this->assertCount(2, $stats[$slot2->id]['signedup']);
        $this->assertCount(1, $stats[$slot3->id]['signedup']);
        $this->assertEquals(2, $stats[$slot1->id]['total']);
        $this->assertEquals(2, $stats[$slot2->id]['total']);
        $this->assertEquals(1, $stats[$slot3->id]['total']);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_tutorial::gettutorialstatss works correctly.
     *
     * @covers mod_tutorialbooking_tutorial::gettutorialstats
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_gettutorialstats() {
        $this->resetAfterTest(true);

        $fakerecord1 = new stdClass();
        $fakerecord1->id = 1;
        $fakerecord1->tutorialid = 6;
        $fakerecord1->description = 'Test Description';
        $fakerecord1->descformat = FORMAT_PLAIN;
        $fakerecord1->location = 'Some room';
        $fakerecord1->spaces = 4;
        $fakerecord1->sequence = 1;
        $fakerecord1->visible = 1;
        $fakerecord1->usercreated = 2;
        $fakerecord1->timecreated = time();
        $fakerecord1->timemodified = $fakerecord1->timecreated;

        $fakerecord2 = clone($fakerecord1);
        $fakerecord2->id = 7;
        $fakerecord2->description = 'Test 2';
        $fakerecord2->spaces = 2;
        $fakerecord2->sequence = 2;

        $fakerecord3 = clone($fakerecord1);
        $fakerecord3->id = 3;
        $fakerecord3->description = 'Test 3';
        $fakerecord3->spaces = 3;
        $fakerecord3->sequence = 3;

        // Setup data for the test.
        $stats = array(
            '1' => $fakerecord1,
            '7' => $fakerecord2,
            '3' => $fakerecord3
        );

        $signups = array(
            '1' => array(
                'waiting' => array(
                    array('id' => 1, 'fname' => 'Joe Bloggs')
                ),
                'signedup' => array(
                    array('id' => 1, 'fname' => 'Jane Bloggs'),
                    array('id' => 1, 'fname' => 'Victoria Sax-Coberg')
                ),
                'total' => 3
            ),
            '7' => array(
                'waiting' => array(),
                'signedup' => array(
                    array('id' => 1, 'fname' => 'Jack Beanstalk'),
                    array('id' => 1, 'fname' => 'George Greenfield')
                ),
                'total' => 2
            ),
            '3' => array(
                'waiting' => array(
                    array('id' => 1, 'fname' => 'Edith Parker')
                ),
                'signedup' => array(),
                'total' => 1
            ),
        );

        // Start the test.
        $result = mod_tutorialbooking_tutorial::gettutorialstats($stats, $signups);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('places', $result);
        $this->assertArrayHasKey('waiting', $result);
        $this->assertArrayHasKey('signedup', $result);

        $this->assertEquals(9, $result['places']);
        $this->assertEquals(2, $result['waiting']);
        $this->assertEquals(4, $result['signedup']);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Given a tutorialbooking record, it checks that the record is still the same in the database.
     * assertEquals cannot be used as the generated tutorialbooking object it returns contains more
     * than just the tutorialbooking database table information.
     *
     * @global moodle_database $DB The Moodle database connection object.
     * @param stdClass $tutorialrecord The tutorialbooking record created by the generator.
     * @return void
     */
    protected function verify_tutorial_record_unchanged($tutorialrecord) {
        global $DB;
        $databaserecorrd = $DB->get_record('tutorialbooking', array('id' => $tutorialrecord->id));
        $this->assertAttributeEquals($tutorialrecord->id, 'id', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->course, 'course', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->name, 'name', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->intro, 'intro', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->introformat, 'introformat', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->timecreated, 'timecreated', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->timemodified, 'timemodified', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->completionsignedup, 'completionsignedup', $databaserecorrd);
        $this->assertAttributeEquals($tutorialrecord->privacy, 'privacy', $databaserecorrd);
    }
}