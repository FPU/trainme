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
 * Tests the tutorial booking mod_tutorialbooking_user class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_user_testcase extends advanced_testcase {
    /**
     * Tests that mod_tutorialbooking_user::adduser works correctly.
     *
     * @covers mod_tutorialbooking_user::adduser
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_adduser() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $student = $DB->get_record('role', array('shortname' => 'student'));
        $teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user0->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($user1->id, $course->id, $student->id);
        self::getDataGenerator()->enrol_user($user2->id, $course->id, $teacher->id); // Teacher.

        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $coursemodule = get_coursemodule_from_instance('tutorialbooking', $tutorialbooking->id);
        $tutorialcontext = context_module::instance($coursemodule->id);
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 2));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $coursemodule2 = get_coursemodule_from_instance('tutorialbooking', $tutorialbooking2->id);
        $tutorialcontext2 = context_module::instance($coursemodule2->id);
        $slot3 = $generator->add_slot($tutorialbooking2, array('spaces' => 1));
        $generator->add_slot($tutorialbooking2, array('spaces' => 1));

        // Locked tutorial.
        $tutorialbooking3 = $generator->create_instance(array('course' => $course->id, 'locked' => 1));
        $coursemodule3 = get_coursemodule_from_instance('tutorialbooking', $tutorialbooking3->id);
        $tutorialcontext3 = context_module::instance($coursemodule3->id);
        $slot5 = $generator->add_slot($tutorialbooking3, array('spaces' => 1));
        $generator->add_slot($tutorialbooking3, array('spaces' => 1));

        $url = new moodle_url('/');

        $completion = self::createMock('completion_info', array('is_enabled', 'update_state'), array($course));
        // Force completion to not be enabled, we expect it to be called.
        $completion->expects($this->atLeastOnce())
                ->method('is_enabled')
                ->will($this->returnValue(false));
        // Ensure that the completion state is not updated, because it is disabled.
        $completion->expects($this->never())
                ->method('update_state')
                ->withAnyParameters();

        $this->setUser($user0);

        // There should be no signups.
        $this->assertEquals(0, $DB->count_records('tutorialbooking_signups'));

        mod_tutorialbooking_user::adduser($user0->id, $slot1, $tutorialbooking, $tutorialcontext, $completion, $coursemodule, $url, true);
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups'));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups',
                array('courseid' => $course->id, 'userid' => $user0->id, 'tutorialid' => $tutorialbooking->id, 'sessionid' => $slot1->id)));

        // Check that a user cannot sign up into a slot they have already signed upto.
        try {
            mod_tutorialbooking_user::adduser($user0->id, $slot1, $tutorialbooking, $tutorialcontext, $completion, $coursemodule, $url, true);
            $this->fail('Exception required from mod_tutorialbooking_user::adduser when a user tries to subscribe to a slot twice.');
        } catch (moodle_exception $e) {
            // All is good.
            $this->assertEquals(get_string('useralreadysignedup', 'mod_tutorialbooking', array('id' => $user0->id)), $e->getMessage());
        }
        // The signups should not have changed.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups'));

        // Check that a user cannot sign up a different slot in tutorial booking they have already signed upto.
        try {
            mod_tutorialbooking_user::adduser($user0->id, $slot2, $tutorialbooking, $tutorialcontext, $completion, $coursemodule, $url, true);
            $this->fail('Exception required from mod_tutorialbooking_user::adduser when a user tries to tutorial they have signed up to.');
        } catch (moodle_exception $e) {
            // All is good.
            $this->assertEquals(get_string('useralreadysignedup', 'mod_tutorialbooking', array('id' => $user0->id)), $e->getMessage());
        }
        // The signups should not have changed.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups'));

        // Check that a user cannot sign up a locked tutorial booking.
        try {
            mod_tutorialbooking_user::adduser($user0->id, $slot5, $tutorialbooking3, $tutorialcontext3, $completion, $coursemodule3, $url, true);
            $this->fail('Exception required from mod_tutorialbooking_user::adduser when a user tries to a locked tutorial.');
        } catch (moodle_exception $e) {
            // All is good.
            $this->assertEquals(get_string('lockederror', 'mod_tutorialbooking', array('id' => $user0->id)), $e->getMessage());
        }
        // The signups should not have changed.
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups'));

        // Check that a user who is signed up in one tutorial booking can signup to a slot in a second tutorial booking.
        mod_tutorialbooking_user::adduser($user0->id, $slot3, $tutorialbooking2, $tutorialcontext2, $completion, $coursemodule2, $url, true);
        $this->assertEquals(2, $DB->count_records('tutorialbooking_signups'));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups',
                array('courseid' => $course->id, 'userid' => $user0->id, 'tutorialid' => $tutorialbooking2->id, 'sessionid' => $slot3->id)));

        // Check that a second user can signup to a slot successfully.
        mod_tutorialbooking_user::adduser($user1->id, $slot1, $tutorialbooking, $tutorialcontext, $completion, $coursemodule, $url, true);
        $this->assertEquals(3, $DB->count_records('tutorialbooking_signups'));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups',
                array('courseid' => $course->id, 'userid' => $user1->id, 'tutorialid' => $tutorialbooking->id, 'sessionid' => $slot1->id)));

        // Check that a user cannot sign up a slot with no free spaces.
        try {
            mod_tutorialbooking_user::adduser($user1->id, $slot3, $tutorialbooking2, $tutorialcontext2, $completion, $coursemodule2, $url, true);
            $this->fail('Exception required from mod_tutorialbooking_user::adduser when a user tries to subscribe to a full slot.');
        } catch (moodle_exception $e) {
            // All is good.
            $this->assertEquals(get_string('sessionfull', 'mod_tutorialbooking', array('id' => $user0->id)), $e->getMessage());
        }
        // The signups should not have changed.
        $this->assertEquals(3, $DB->count_records('tutorialbooking_signups'));

        // Check that a user without the capability to sign up cannot be added.
        try {
            mod_tutorialbooking_user::adduser($user2->id, $slot2, $tutorialbooking, $tutorialcontext, $completion, $coursemodule, $url, true);
            $this->fail('Exception required from mod_tutorialbooking_user::adduser when a unauthorised user subscribes.');
        } catch (moodle_exception $e) {
            // All is good.
            $this->assertEquals(get_string('unauthorised', 'mod_tutorialbooking'), $e->getMessage());
        }
        // The signups should not have changed.
        $this->assertEquals(3, $DB->count_records('tutorialbooking_signups'));

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_user::addusers_from_form works correctly.
     *
     * @covers mod_tutorialbooking_user::addusers_from_form
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_addusers_from_form() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $student = $DB->get_record('role', array('shortname' => 'student'));
        $teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $noneditingteacher = $DB->get_record('role', array('shortname' => 'teacher'));

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user0->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($user1->id, $course->id, $student->id);
        self::getDataGenerator()->enrol_user($user2->id, $course->id, $noneditingteacher->id); // Teacher (non-editing).
        self::getDataGenerator()->enrol_user($user3->id, $course->id, $teacher->id); // Teacher.

        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $coursemodule = get_coursemodule_from_instance('tutorialbooking', $tutorialbooking->id);
        $tutorialcontext = context_module::instance($coursemodule->id);
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 1));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $coursemodule2 = get_coursemodule_from_instance('tutorialbooking', $tutorialbooking2->id);
        $tutorialcontext2 = context_module::instance($coursemodule2->id);
        $slot3 = $generator->add_slot($tutorialbooking2, array('spaces' => 1));
        $generator->add_slot($tutorialbooking2, array('spaces' => 3));

        $completion = self::createMock('completion_info', array('is_enabled', 'update_state'), array($course));
        // Force completion to not be enabled, we expect it to be called.
        $completion->expects($this->atLeastOnce())
                ->method('is_enabled')
                ->will($this->returnValue(false));
        // Ensure that the completion state is not updated, because it is disabled.
        $completion->expects($this->never())
                ->method('update_state')
                ->withAnyParameters();

        $this->assertEquals(0, $DB->count_records('tutorialbooking_signups'));

        // Check it does not allow a non-editing teacher to over subscribe.
        $this->setUser($user2);
        try {
            mod_tutorialbooking_user::addusers_from_form($course->id, $tutorialbooking, $tutorialcontext, $completion,
                    $coursemodule, $slot1->id, array($user0->id, $user1->id));
            $this->fail();
        } catch (moodle_exception $e) {
            // We wanted the exception.
            $this->assertEquals(0, $DB->count_records('tutorialbooking_signups'));
        }
        // It should pass for a slot that is not oversubscribed though.
        mod_tutorialbooking_user::addusers_from_form($course->id, $tutorialbooking, $tutorialcontext, $completion,
                $coursemodule, $slot2->id, array($user0->id, $user1->id));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_signups'));

        $this->setUser($user3);
        // A editing teacher should be able to oversubscribe.
        mod_tutorialbooking_user::addusers_from_form($course->id, $tutorialbooking2, $tutorialcontext2, $completion,
                $coursemodule2, $slot3->id, array($user0->id, $user1->id));
        $this->assertEquals(4, $DB->count_records('tutorialbooking_signups'));

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_user::generate_removeuser_formdata works correctly.
     *
     * @covers mod_tutorialbooking_user::generate_removeuser_formdata
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_generate_removeuser_formdata() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $student = $DB->get_record('role', array('shortname' => 'student'));
        $teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user0->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($user1->id, $course->id, $student->id);
        self::getDataGenerator()->enrol_user($user2->id, $course->id, $student->id);
        self::getDataGenerator()->enrol_user($user3->id, $course->id, $teacher->id); // Teacher.

        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 1));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $user0);
        $generator->signup_user($tutorialbooking, $slot2, $user1);
        $generator->signup_user($tutorialbooking, $slot2, $user2);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $generator->add_slot($tutorialbooking2, array('spaces' => 1));
        $slot4 = $generator->add_slot($tutorialbooking2, array('spaces' => 3));
        $generator->signup_user($tutorialbooking2, $slot4, $user0);
        $generator->signup_user($tutorialbooking2, $slot4, $user1);
        $generator->signup_user($tutorialbooking2, $slot4, $user2);

        // Check all the signups are correctly created.
        $this->assertEquals(6, $DB->count_records('tutorialbooking_signups'));

        $forminfo = mod_tutorialbooking_user::generate_removeuser_formdata($tutorialbooking->id, $course->id, $user0->id);
        $this->assertArrayHasKey('userid', $forminfo);
        $this->assertArrayHasKey('username', $forminfo);
        $this->assertArrayHasKey('timeslotname', $forminfo);
        $this->assertArrayHasKey('timeslotid', $forminfo);
        $this->assertArrayHasKey('tutorialid', $forminfo);
        $this->assertArrayHasKey('courseid', $forminfo);

        $this->assertEquals($user0->id, $forminfo['userid']);
        $this->assertEquals($user0->firstname.' '.$user0->lastname.' ('.$user0->username.')', $forminfo['username']);
        $this->assertEquals($slot1->description, $forminfo['timeslotname']);
        $this->assertEquals($slot1->id, $forminfo['timeslotid']);
        $this->assertEquals($tutorialbooking->id, $forminfo['tutorialid']);
        $this->assertEquals($course->id, $forminfo['courseid']);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_user::remove_user works correctly.
     *
     * @covers mod_tutorialbooking_user::remove_user
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_remove_user() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $this->setAdminUser();

        $student = $DB->get_record('role', array('shortname' => 'student'));
        $teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user0->id, $course->id, $student->id); // Students.
        self::getDataGenerator()->enrol_user($user1->id, $course->id, $student->id);
        self::getDataGenerator()->enrol_user($user2->id, $course->id, $student->id);
        self::getDataGenerator()->enrol_user($user3->id, $course->id, $teacher->id); // Teacher.

        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $coursemodule = get_coursemodule_from_instance('tutorialbooking', $tutorialbooking->id);
        $slot1 = $generator->add_slot($tutorialbooking, array('spaces' => 1));
        $slot2 = $generator->add_slot($tutorialbooking, array('spaces' => 3));
        $generator->signup_user($tutorialbooking, $slot1, $user0);
        $generator->signup_user($tutorialbooking, $slot2, $user1);
        $generator->signup_user($tutorialbooking, $slot2, $user2);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));
        $generator->add_slot($tutorialbooking2, array('spaces' => 1));
        $slot4 = $generator->add_slot($tutorialbooking2, array('spaces' => 3));
        $generator->signup_user($tutorialbooking2, $slot4, $user0);
        $generator->signup_user($tutorialbooking2, $slot4, $user1);
        $generator->signup_user($tutorialbooking2, $slot4, $user2);

        $completion = self::createMock('completion_info', array('is_enabled', 'update_state'), array($course));
        // Force completion to not be enabled, we expect it to be called.
        $completion->expects($this->atLeastOnce())
                ->method('is_enabled')
                ->will($this->returnValue(false));
        // Ensure that the completion state is not updated, because it is disabled.
        $completion->expects($this->never())
                ->method('update_state')
                ->withAnyParameters();

        // Check all the signups are correctly created.
        $this->assertEquals(6, $DB->count_records('tutorialbooking_signups'));

        $result = mod_tutorialbooking_user::remove_user($user0->id, $tutorialbooking, $completion, $coursemodule, false);
        $this->assertAttributeEquals($user0->id, 'user', $result);
        $this->assertAttributeEquals(null, 'timeslot', $result);
        $this->assertEquals(5, $DB->count_records('tutorialbooking_signups'));
        $this->assertEquals(2, $DB->count_records('tutorialbooking_signups', array('tutorialid' => $tutorialbooking->id)));
        $this->assertEquals(3, $DB->count_records('tutorialbooking_signups', array('tutorialid' => $tutorialbooking2->id)));

        // Test messages are sent if desired.
        unset_config('noemailever');
        set_config('liveservice', true, 'tutorialbooking');
        $sink = $this->redirectEmails();

        $msg = array('text' => 'Test message', 'format' => FORMAT_PLAIN);
        $result2 = mod_tutorialbooking_user::remove_user($user1->id, $tutorialbooking, $completion, $coursemodule, true, $msg);
        $this->assertAttributeEquals($user1->id, 'user', $result2);
        $this->assertAttributeEquals($slot2->id, 'timeslot', $result2);
        $this->assertEquals(4, $DB->count_records('tutorialbooking_signups'));
        $this->assertEquals(1, $DB->count_records('tutorialbooking_signups', array('tutorialid' => $tutorialbooking->id)));
        $this->assertEquals(3, $DB->count_records('tutorialbooking_signups', array('tutorialid' => $tutorialbooking2->id)));

        $sentmessages = $sink->get_messages();
        $this->assertEquals(1, count($sentmessages));
        $sink->close();

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_user::displayusernames works correctly.
     *
     * @covers mod_tutorialbooking_user::displayusernames
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_displayusernames() {
        $this->resetAfterTest(true);

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->create_user();

        $params = array($user0->id, $user1->id, $user2->id);

        $result = mod_tutorialbooking_user::displayusernames($params);

        $this->assertContains($user0->firstname.' '.$user0->lastname.' ('.$user0->username.')', $result);
        $this->assertContains($user1->firstname.' '.$user1->lastname.' ('.$user1->username.')', $result);
        $this->assertContains($user2->firstname.' '.$user2->lastname.' ('.$user2->username.')', $result);

        $this->assertDebuggingNotCalled();
    }
}
