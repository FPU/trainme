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
 * Tests the tutorial booking mod_tutorialbooking_message class.
 *
 * @package     mod_tutorialbooking
 * @copyright   University of Nottingham, 2014
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_message_testcase extends advanced_testcase {
    /**
     * Tests that mod_tutorialbooking_message::generate_formdata generates a file as expected.
     *
     * @covers mod_tutorialbooking_message::generate_formdata
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_generate_formdata() {
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot = $generator->add_slot($tutorialbooking);

        $formdata = mod_tutorialbooking_message::generate_formdata($course, $tutorialbooking->id, $slot->id);
        $this->assertArrayHasKey('id', $formdata);
        $this->assertEquals($slot->id, $formdata['id']);
        $this->assertArrayHasKey('courseid', $formdata);
        $this->assertEquals($course->id, $formdata['courseid']);
        $this->assertArrayHasKey('title', $formdata);
        $this->assertEquals($course->fullname, $formdata['title']);
        $this->assertArrayHasKey('stitle', $formdata);
        $cleandescription = clean_param($slot->description, PARAM_TEXT);
        $this->assertEquals($cleandescription, $formdata['stitle']);
        $this->assertArrayHasKey('subject', $formdata);
        $this->assertEquals($cleandescription, $formdata['subject']);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_message::generate_formdata generates an exception
     * when the session passed is not for the tutorial specified.
     *
     * @covers mod_tutorialbooking_message::generate_formdata
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_generate_formdata_error() {
        $this->resetAfterTest(true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot = $generator->add_slot($tutorialbooking);

        $tutorialbooking2 = $generator->create_instance(array('course' => $course->id));

        // Should not accept a slot that is not for the tutorial.
        $this->expectException('coding_exception');
        $this->expectExceptionMessage('The session is not for the specified tutorial mod_tutorialbooking_message::generate_formdata');
        mod_tutorialbooking_message::generate_formdata($course, $tutorialbooking2->id, $slot->id);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that mod_tutorialbooking_message::send_message sends a message as expected, and stores the result correctly.
     *
     * @covers mod_tutorialbooking_message::send_message
     * @covers mod_tutorialbooking_message::get_messages
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_send_message() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser(); // The admin user has an id of 2.
        $generator = self::getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $sink = $this->redirectMessages();

        $user0 = self::getDataGenerator()->create_user();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();
        $user5 = self::getDataGenerator()->create_user();

        $course = self::getDataGenerator()->create_course();
        $tutorialbooking = $generator->create_instance(array('course' => $course->id));
        $slot = $generator->add_slot($tutorialbooking);
        $slot2 = $generator->add_slot($tutorialbooking);
        $generator->signup_user($tutorialbooking, $slot, $user0);
        $generator->signup_user($tutorialbooking, $slot, $user1);
        $generator->signup_user($tutorialbooking, $slot, $user2);
        $generator->signup_user($tutorialbooking, $slot, $user3);
        $generator->signup_user($tutorialbooking, $slot2, $user4);
        $generator->signup_user($tutorialbooking, $slot2, $user5);

        set_config('liveservice', true, 'tutorialbooking'); // Ensure it is set as a live service.

        // Test sending a message to session.
        $message = array('text' => html_writer::tag('p', 'This is a test message'), 'format' => FORMAT_HTML);
        $subject = 'Test message';
        $returnvalue = mod_tutorialbooking_message::send_message($tutorialbooking, $message, $slot->id, $subject);
        $this->assertEquals(get_string('liveservicemsg', 'tutorialbooking'), $returnvalue);
        $messages = $sink->get_messages();
        $this->assertCount(4, $messages);
        $this->verify_message($subject, $message['text'], $user0->id, 2, $messages[0]);
        $this->verify_message($subject, $message['text'], $user1->id, 2, $messages[1]);
        $this->verify_message($subject, $message['text'], $user2->id, 2, $messages[2]);
        $this->verify_message($subject, $message['text'], $user3->id, 2, $messages[3]);
        $sink->clear();
        $this->assertEquals(1, $DB->count_records('tutorialbooking_messages'));

        $this->setUser($user5->id);

        // Test sending a message to an individual user.
        $message2 = array('text' => html_writer::tag('p', 'What a wonderful message to send.'), 'format' => FORMAT_HTML);
        $subject2 = 'This is a subject';
        $returnvalue2 = mod_tutorialbooking_message::send_message($tutorialbooking, $message2, array($user0), $subject2);
        $this->assertEquals(get_string('liveservicemsg', 'tutorialbooking'), $returnvalue2);
        $messages2 = $sink->get_messages();
        $this->assertCount(1, $messages2);
        $this->verify_message($subject2, $message2['text'], $user0->id, $user5->id, $messages2[0]);
        $sink->clear();
        $this->assertEquals(2, $DB->count_records('tutorialbooking_messages'));

        $this->setAdminUser();

        // Get the first page of upto 30 messages this user has sent.
        $results = mod_tutorialbooking_message::get_messages($tutorialbooking->id, $course->id, 0, 0, 30);
        $this->assertAttributeInstanceOf('\mod_tutorialbooking\renderable\messages', 'messages', $results);
        $this->assertEquals(1, $results->totalmessages);
        $this->assertAttributeCount($results->totalmessages, 'messages', $results->messages);
        $archivemessage = $results->messages->messages[0];
        $this->assertEquals(\mod_tutorialbooking_user::displayusernames(array(2)), $archivemessage['sentby']);
        $this->assertEquals($subject, $archivemessage['subject']);
        $this->assertEquals($message['text'], $archivemessage['message']);
        $senttousers = \mod_tutorialbooking_user::displayusernames(array($user0->id, $user1->id, $user2->id, $user3->id));
        $this->assertEquals($senttousers, $archivemessage['sentto']);

        // Get the first page of upto 30 messages of all messages sent by the tutorial booking.
        $results2 = mod_tutorialbooking_message::get_messages($tutorialbooking->id, $course->id,
                mod_tutorialbooking_message::VIEWALLMESSAGES, 0, 30);
        $this->assertAttributeInstanceOf('\mod_tutorialbooking\renderable\messages', 'messages', $results2);
        $this->assertEquals(2, $results2->totalmessages);
        $this->assertAttributeCount($results2->totalmessages, 'messages', $results2->messages);

        // Check the paging.
        $results3 = mod_tutorialbooking_message::get_messages($tutorialbooking->id, $course->id,
                mod_tutorialbooking_message::VIEWALLMESSAGES, 0, 1);
        $this->assertAttributeInstanceOf('\mod_tutorialbooking\renderable\messages', 'messages', $results3);
        $this->assertEquals(2, $results3->totalmessages);
        $this->assertAttributeCount(1, 'messages', $results3->messages);

        $results4 = mod_tutorialbooking_message::get_messages($tutorialbooking->id, $course->id,
                mod_tutorialbooking_message::VIEWALLMESSAGES, 1, 1);
        $this->assertAttributeInstanceOf('\mod_tutorialbooking\renderable\messages', 'messages', $results4);
        $this->assertEquals(2, $results4->totalmessages);
        $this->assertAttributeCount(1, 'messages', $results4->messages);

        $this->assertDebuggingNotCalled();
    }

    /**
     * Tests that a message is correct.
     *
     * @param string $subject The expected subject of the message.
     * @param string $messagetext The expectec text of the message.
     * @param int $touserid The expected id of the user the message was sent to.
     * @param int $fromuserid The expected id of the user the message was sent by.
     * @param stdClass $message The message being tested.
     */
    protected function verify_message($subject, $messagetext, $touserid, $fromuserid, stdClass $message) {
        $this->assertAttributeEquals($subject, 'subject', $message);
        $this->assertAttributeEquals($messagetext, 'fullmessage', $message);
        $this->assertAttributeEquals($touserid, 'useridto', $message);
        $this->assertAttributeEquals($fromuserid, 'useridfrom', $message);
    }
}
