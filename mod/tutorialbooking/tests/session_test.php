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
 * Tests the tutorial booking mod_tutorialbooking_session class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_session_testcase extends advanced_testcase {
    /**
     * Tests that mod_tutorialbooking_session::computesequence works correctly.
     *
     * @covers mod_tutorialbooking_session::computesequence
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_computesequence() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $slot3 = $generator->add_slot($tutorialbooking);
        $slot4 = $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2);
        $slot6 = $generator->add_slot($tutorialbooking2);
        $slot7 = $generator->add_slot($tutorialbooking2);

        // Check that trying to move the last slot to a higher sequence changes nothing.
        $this->assertEquals($slot4->sequence,
                mod_tutorialbooking_session::computesequence($slot4->sequence, $slot4->sequence + 1, $tutorialbooking->id));
        $this->assertEquals($slot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));

        // Check that trying to move the first slot down one does nothing.
        $this->assertEquals(1, $slot1->sequence); // Must be slot 1.
        $this->assertEquals($slot1->sequence,
                mod_tutorialbooking_session::computesequence($slot1->sequence, $slot1->sequence - 1, $tutorialbooking->id));
        $this->assertEquals($slot1, $DB->get_record('tutorialbooking_sessions', array('id' => $slot1->id)));

        // Check that moving a session up one works correctly.
        $this->assertEquals($slot2->sequence,
                mod_tutorialbooking_session::computesequence($slot1->sequence, $slot2->sequence, $tutorialbooking->id));
        // The session to be moved should have it's id moved down by 1. It will be moved to the correct position elsewhere.
        $newslot1 = $slot1;
        $newslot1->sequence = $slot1->sequence - 1;
        $this->assertEquals($newslot1, $DB->get_record('tutorialbooking_sessions', array('id' => $slot1->id)));
        // Slot 2 should now be in it's final position.
        $newslot2 = $slot2;
        $newslot2->sequence = $slot2->sequence - 1;
        $this->assertEquals($newslot2, $DB->get_record('tutorialbooking_sessions', array('id' => $slot2->id)));

        // Check that no other slots have been affected.
        $this->assertEquals($slot3, $DB->get_record('tutorialbooking_sessions', array('id' => $slot3->id)));
        $this->assertEquals($slot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));
        $this->assertEquals($slot5, $DB->get_record('tutorialbooking_sessions', array('id' => $slot5->id)));
        $this->assertEquals($slot6, $DB->get_record('tutorialbooking_sessions', array('id' => $slot6->id)));
        $this->assertEquals($slot7, $DB->get_record('tutorialbooking_sessions', array('id' => $slot7->id)));

        // Check that moving a session down one works correctly.
        $this->assertEquals($slot3->sequence,
                mod_tutorialbooking_session::computesequence($slot4->sequence, $slot3->sequence, $tutorialbooking->id));
        $newslot3 = $slot3;
        $newslot3->sequence = $slot3->sequence + 1;
        $this->assertEquals($newslot3, $DB->get_record('tutorialbooking_sessions', array('id' => $slot3->id)));
        $newslot4 = $slot4;
        $newslot4->sequence = $slot4->sequence + 1;
        $this->assertEquals($newslot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));

        // Check that moving multiple slots works correctly.
        $this->assertEquals($slot5->sequence,
                mod_tutorialbooking_session::computesequence($slot7->sequence, $slot5->sequence, $tutorialbooking2->id));
        $newslot5 = $slot5;
        $newslot5->sequence = $slot5->sequence + 1;
        $this->assertEquals($newslot5, $DB->get_record('tutorialbooking_sessions', array('id' => $slot5->id)));
        $newslot6 = $slot6;
        $newslot6->sequence = $slot6->sequence + 1;
        $this->assertEquals($newslot6, $DB->get_record('tutorialbooking_sessions', array('id' => $slot6->id)));
        $newslot7 = $slot7;
        $newslot7->sequence = $slot7->sequence + 1;
        $this->assertEquals($newslot7, $DB->get_record('tutorialbooking_sessions', array('id' => $slot7->id)));

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_session::delete_session works correctly.
     *
     * @covers mod_tutorialbooking_session::delete_session
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_delete_session() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $slot3 = $generator->add_slot($tutorialbooking);
        $slot4 = $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2);
        $slot6 = $generator->add_slot($tutorialbooking2);
        $slot7 = $generator->add_slot($tutorialbooking2);

        $this->assertEquals(7, $DB->count_records('tutorialbooking_sessions'));

        // Use the delete delete function.
        mod_tutorialbooking_session::delete_session($tutorialbooking->id, $slot2->id);
        $this->assertEquals(6, $DB->count_records('tutorialbooking_sessions'));
        $this->assertFalse($DB->get_record('tutorialbooking_sessions', array('id' => $slot2->id)));

        // The first slot should not be affected.
        $this->assertEquals($slot1, $DB->get_record('tutorialbooking_sessions', array('id' => $slot1->id)));
        // The slots after the deleted one should have their sequence reduced by 1.
        $newslot3 = $slot3;
        $newslot3->sequence = $slot3->sequence - 1;
        $this->assertEquals($newslot3, $DB->get_record('tutorialbooking_sessions', array('id' => $slot3->id)));
        $newslot4 = $slot4;
        $newslot4->sequence = $slot4->sequence - 1;
        $this->assertEquals($newslot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));

        // The second tutorial should be entirely unaffected.
        $this->assertEquals($slot5, $DB->get_record('tutorialbooking_sessions', array('id' => $slot5->id)));
        $this->assertEquals($slot6, $DB->get_record('tutorialbooking_sessions', array('id' => $slot6->id)));
        $this->assertEquals($slot7, $DB->get_record('tutorialbooking_sessions', array('id' => $slot7->id)));

        // Test that an error is generated when the tutorial id and the slot id do not match.
        $this->expectException('dml_missing_record_exception');
        mod_tutorialbooking_session::delete_session($tutorialbooking->id, $slot5->id);
        // And that the session record is not deleted.
        $this->assertEquals($slot5, $DB->get_record('tutorialbooking_sessions', array('id' => $slot5->id)));

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_session::generate_editsession_formdata works correctly.
     *
     * @covers mod_tutorialbooking_session::generate_editsession_formdata
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_generate_editsession_formdata() {
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $this->setAdminUser(); // User id = 2.

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $generator->add_slot($tutorialbooking);
        $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $generator->add_slot($tutorialbooking2);
        $generator->add_slot($tutorialbooking2);
        $generator->add_slot($tutorialbooking2);

        // Test with a slot.
        $formdata = mod_tutorialbooking_session::generate_editsession_formdata($course->id, $tutorialbooking, $slot2->id);
        $this->assertTrue(is_array($formdata));
        $formdata = (object) $formdata;
        $this->assertAttributeEquals($slot2->id, 'id', $formdata);
        $this->assertAttributeEquals($tutorialbooking->id, 'tutorialid', $formdata);
        $this->assertAttributeEquals($slot2->description, 'title', $formdata);
        $this->assertAttributeEquals(array('text' => $slot2->summary, 'format' => $slot2->summaryformat), 'summary', $formdata);
        $this->assertAttributeEquals($slot2->usercreated, 'usercreated', $formdata);
        $this->assertAttributeEquals($slot2->spaces, 'spaces', $formdata);
        $this->assertAttributeEquals($slot2->sequence, 'sequence', $formdata);
        $this->assertAttributeEquals($course->id, 'courseid', $formdata);
        $this->assertAttributeInternalType('array', 'positions', $formdata);
        $this->assertCount(4, $formdata->positions);
        $this->assertEquals(get_string('positionfirst', 'tutorialbooking'), $formdata->positions[1]);

        // Test for a new slot.
        $formdata2 = mod_tutorialbooking_session::generate_editsession_formdata($course->id, $tutorialbooking2, 0);
        $this->assertTrue(is_array($formdata2));
        $formdata2 = (object) $formdata2;
        $this->assertAttributeEquals(0, 'id', $formdata2);
        $this->assertAttributeEquals($tutorialbooking2->id, 'tutorialid', $formdata2);
        $this->assertAttributeEquals(2, 'usercreated', $formdata2);
        $this->assertAttributeEquals(get_config('tutorialbooking', 'defaultnumbers'), 'spaces', $formdata2);
        $this->assertAttributeEquals($course->id, 'courseid', $formdata2);
        $this->assertAttributeInternalType('array', 'positions', $formdata2);
        $this->assertCount(4, $formdata2->positions);
        $this->assertEquals(get_string('positionfirst', 'tutorialbooking'), $formdata2->positions[1]);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_session::get_max_sequence_value works correctly.
     *
     * @covers mod_tutorialbooking_session::get_max_sequence_value
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_getsessionstats() {
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
        $slot4 = $generator->add_slot($tutorialbooking, array('spaces' => 69));
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

        $stats1 = mod_tutorialbooking_session::getsessionstats($slot1->id);
        $this->assertAttributeEquals($slot1->spaces, 'places', $stats1);
        $this->assertAttributeEquals(2, 'signedup', $stats1);

        $stats2 = mod_tutorialbooking_session::getsessionstats($slot2->id);
        $this->assertAttributeEquals($slot2->spaces, 'places', $stats2);
        $this->assertAttributeEquals(2, 'signedup', $stats2);

        $stats3 = mod_tutorialbooking_session::getsessionstats($slot3->id);
        $this->assertAttributeEquals($slot3->spaces, 'places', $stats3);
        $this->assertAttributeEquals(1, 'signedup', $stats3);

        $stats4 = mod_tutorialbooking_session::getsessionstats($slot4->id);
        $this->assertAttributeEquals($slot4->spaces, 'places', $stats4);
        $this->assertAttributeEquals(0, 'signedup', $stats4);

        $stats5 = mod_tutorialbooking_session::getsessionstats($slot5->id);
        $this->assertAttributeEquals($slot5->spaces, 'places', $stats5);
        $this->assertAttributeEquals(1, 'signedup', $stats5);

        $stats6 = mod_tutorialbooking_session::getsessionstats($slot6->id);
        $this->assertAttributeEquals($slot6->spaces, 'places', $stats6);
        $this->assertAttributeEquals(2, 'signedup', $stats6);

        $stats7 = mod_tutorialbooking_session::getsessionstats($slot7->id);
        $this->assertAttributeEquals($slot7->spaces, 'places', $stats7);
        $this->assertAttributeEquals(3, 'signedup', $stats7);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_session::move_sequence_down works correctly.
     *
     * @covers mod_tutorialbooking_session::move_sequence_down
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_move_sequence_down() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $slot3 = $generator->add_slot($tutorialbooking);
        $slot4 = $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2);
        $slot6 = $generator->add_slot($tutorialbooking2);
        $slot7 = $generator->add_slot($tutorialbooking2);

        // Slot 2 and 3 should have their sequence swapped.
        $this->assertTrue(mod_tutorialbooking_session::move_sequence_down($tutorialbooking->id, $slot2->sequence));
        $this->assertEquals($slot3->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot2->id)));
        $this->assertEquals($slot2->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot3->id)));

        // Check all the other slots are unchanged.
        $this->assertEquals($slot1, $DB->get_record('tutorialbooking_sessions', array('id' => $slot1->id)));
        $this->assertEquals($slot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));
        $this->assertEquals($slot5, $DB->get_record('tutorialbooking_sessions', array('id' => $slot5->id)));
        $this->assertEquals($slot6, $DB->get_record('tutorialbooking_sessions', array('id' => $slot6->id)));
        $this->assertEquals($slot7, $DB->get_record('tutorialbooking_sessions', array('id' => $slot7->id)));

        // Slots 1 and 3 should have their sequence swapped (remember slot 3 has the original slot 2 sequence now).
        $this->assertTrue(mod_tutorialbooking_session::move_sequence_down($tutorialbooking->id, $slot1->sequence));
        $this->assertEquals($slot2->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot1->id)));
        $this->assertEquals($slot1->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot3->id)));

        // Check what happens when we try to move slot 4... it is at the end of the list so should not move down more.
        $this->assertFalse(mod_tutorialbooking_session::move_sequence_down($tutorialbooking->id, $slot4->sequence));

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_session::move_sequence_up works correctly.
     *
     * @covers mod_tutorialbooking_session::move_sequence_up
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_move_sequence_up() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $slot3 = $generator->add_slot($tutorialbooking);
        $slot4 = $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot5 = $generator->add_slot($tutorialbooking2);
        $slot6 = $generator->add_slot($tutorialbooking2);
        $slot7 = $generator->add_slot($tutorialbooking2);

        // Slot 2 and 3 should have their sequence swapped.
        $this->assertTrue(mod_tutorialbooking_session::move_sequence_up($tutorialbooking->id, $slot3->sequence));
        $this->assertEquals($slot3->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot2->id)));
        $this->assertEquals($slot2->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot3->id)));

        // Check all the other slots are unchanged.
        $this->assertEquals($slot1, $DB->get_record('tutorialbooking_sessions', array('id' => $slot1->id)));
        $this->assertEquals($slot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));
        $this->assertEquals($slot5, $DB->get_record('tutorialbooking_sessions', array('id' => $slot5->id)));
        $this->assertEquals($slot6, $DB->get_record('tutorialbooking_sessions', array('id' => $slot6->id)));
        $this->assertEquals($slot7, $DB->get_record('tutorialbooking_sessions', array('id' => $slot7->id)));

        // Slots 2 and 4 should have their sequence swapped (remember slot 2 has the original slot 3 sequence now).
        $this->assertTrue(mod_tutorialbooking_session::move_sequence_up($tutorialbooking->id, $slot4->sequence));
        $this->assertEquals($slot3->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot4->id)));
        $this->assertEquals($slot4->sequence, $DB->get_field('tutorialbooking_sessions', 'sequence', array('id' => $slot2->id)));

        // Check what happens when we try to move slot 1... it is at the start of the list so should not move up more.
        $this->assertFalse(mod_tutorialbooking_session::move_sequence_up($tutorialbooking->id, $slot1->sequence));

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_session::togglevisiblity works correctly.
     *
     * @covers mod_tutorialbooking_session::togglevisiblity
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_togglevisiblity() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('visible' => 0));
        $slot2 = $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $slot3 = $generator->add_slot($tutorialbooking2);
        $slot4 = $generator->add_slot($tutorialbooking2);

        // Check the record is initially invisible.
        $this->assertEquals(0, $DB->get_field('tutorialbooking_sessions', 'visible', array('id' => $slot1->id)));

        // Check that setting to visible functions.
        $this->assertTrue(mod_tutorialbooking_session::togglevisiblity($slot1->id, true));
        $this->assertEquals(1, $DB->get_field('tutorialbooking_sessions', 'visible', array('id' => $slot1->id)));
        // Check all the other slots are unchanged.
        $this->assertEquals($slot2, $DB->get_record('tutorialbooking_sessions', array('id' => $slot2->id)));
        $this->assertEquals($slot3, $DB->get_record('tutorialbooking_sessions', array('id' => $slot3->id)));
        $this->assertEquals($slot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));

        // Check that setting to visible functions.
        $this->assertTrue(mod_tutorialbooking_session::togglevisiblity($slot1->id, false));
        $this->assertEquals(0, $DB->get_field('tutorialbooking_sessions', 'visible', array('id' => $slot1->id)));
        // Check all the other slots are unchanged.
        $this->assertEquals($slot2, $DB->get_record('tutorialbooking_sessions', array('id' => $slot2->id)));
        $this->assertEquals($slot3, $DB->get_record('tutorialbooking_sessions', array('id' => $slot3->id)));
        $this->assertEquals($slot4, $DB->get_record('tutorialbooking_sessions', array('id' => $slot4->id)));

        $this->assertDebuggingNotCalled();
    }
}
