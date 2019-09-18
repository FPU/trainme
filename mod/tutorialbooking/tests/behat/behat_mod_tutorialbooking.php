<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * Steps definitions related with the tutorial booking activity.
 *
 * @package    mod_tutorialbooking
 * @category   test
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2014 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Behat\Exception\PendingException as PendingException;
/**
 * Tutorial booking related steps definitions.
 *
 * @package    mod_tutorialbooking
 * @category   test
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright  2014 University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_tutorialbooking extends behat_base {
    /**
     * Generates the xpath reqiired to select a named slot in a page.
     *
     * @param string $slotname
     * @return string The xpath selector.
     */
    protected function generate_xpath_slot_selector($slotname) {
        // XPath to a tutorial booking slot.
        // The selector is looking to select an element with the .tutorial_session class
        // that contains an element with the .sectionname class containing the defined slotname.
        return "//*[contains(concat(' ', normalize-space(@class), ' '), ' tutorial_session ') "
                . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' sectionname ') "
                . "and text()[contains(., '" .$this->escape($slotname). "')]]]";
    }

    /**
     * Adds a timeslot to a tutorial booking specified by its name.
     *
     * @Given /^I add a new timeslot to "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking with:$/
     * @param string $tutorialname
     * @param \Behat\Gherkin\Node\TableNode $settings
     * @return void
     */
    public function i_add_a_new_timeslot_to_tutorial_booking_with($tutorialname, TableNode $settings) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::click_link', array(get_string('newtimslotprompt', 'mod_tutorialbooking')));
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', array($settings));
        $this->execute('behat_forms::press_button', array(get_string('saveasnew', 'mod_tutorialbooking')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Presses cancel on the removal confirmation form for a user with the supplied username on a named sklot of the named tutorial booking.
     *
     * @When /^I cancel the removal of "(?P<username_string>(?:[^"]|\\")*)" from "(?P<slot_name_string>(?:[^"]|\\")*)" of "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking with "(?P<message_string>(?:[^"]|\\")*)" as a reason$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $username The user4name of a Moodle user to be removed.
     * @param string $slotname The name of a slot in the tutorial the user is in.
     * @param string $tutorialname The name of the tutorial booking instance.
     * @param string $message The message that should be entered into the confirmtion removal form.
     * @return void
     */
    public function i_cancel_the_removal_of_student_from_slot_of_tutorial_booking_with_reason($username, $slotname, $tutorialname, $message) {
        global $DB;

        // Get the details of the user.
        $name = $DB->get_field('user', $DB->sql_concat_join("' '", array('firstname', 'lastname')), array('username' => $username));

        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array($name, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_field_to', array('message[text]', $message));
        $this->execute('behat_general::i_click_on', array(get_string('cancel'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Create a timetable booking slot by editing a names slot in a named tutorial booking and clicking 'Save as new'
     *
     * @Given /^I create new slot based on "(?P<slot_name_string>(?:[^"]|\\")*)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking with:$/
     * @param string $slotname
     * @param string $tutorialname
     * @param \Behat\Gherkin\Node\TableNode $settings
     * @return void
     */
    public function i_create_new_slot_based_on_in_tutorial_booking_with($slotname, $tutorialname, TableNode $settings) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array(get_string('editsession', 'mod_tutorialbooking'), 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', array($settings));
        $this->execute('behat_forms::press_button', array(get_string('saveasnew', 'mod_tutorialbooking')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Delete a named time slot from a named tutorial booking.
     *
     * @When /^I delete "(?P<slot_name_string>(?:[^"]|\\")*)" of "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $slotname The name of a time slot.
     * @param string $tutorialname The name of a tutorial booking activity.
     * @return void
     */
    public function i_delete_slot_of_tutorial_booking($slotname, $tutorialname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array(get_string('deletesession', 'mod_tutorialbooking'), 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::press_button', array(get_string('continue')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Change the settings of a named timeslot in a named tutorial booking activity.
     *
     * @When /^I edit "(?P<slot_name_string>(?:[^"]|\\")*)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking with:$/
     * @param string $slotname The name of a timeslot.
     * @param string $tutorialname The name of a tutorial booking activity.
     * @param \Behat\Gherkin\Node\TableNode $settings The settings to be changed for the tutorial booking.
     * @return void
     */
    public function i_edit_slot_in_tutorial_booking_with($slotname, $tutorialname, TableNode $settings) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array(get_string('editsession', 'mod_tutorialbooking'), 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', array($settings));
        $this->execute('behat_forms::press_button', array(get_string('save', 'mod_tutorialbooking')));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * The user will remove their sign up from the named tutorial booking activity.
     *
     * @When /^I remove my sign up from "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $tutorialname The name of the tutorial booking the user will be removed from.
     * @return void
     */
    public function i_remove_my_sign_up_from_tutorial_booking($tutorialname) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on', array(get_string('removefromslot', 'mod_tutorialbooking'), 'link'));
        $this->execute('behat_general::i_click_on', array(get_string('continue'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Click on the remove from from slot button, but cancel the process at the confirmation screen.
     *
     * @When /^I cancel remove my sign up from "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $tutorialname The name of the tutorial booking the user will be removed from.
     * @return void
     */
    public function i_cancel_remove_my_sign_up_from_tutorial_booking($tutorialname) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on', array(get_string('removefromslot', 'mod_tutorialbooking'), 'link'));
        $this->execute('behat_general::i_click_on', array(get_string('cancel'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Remove a student by username from a names timeslot on a named tutorial booking sending them a message.
     *
     * @When /^I remove "(?P<username_string>(?:[^"]|\\")*)" from "(?P<slot_name_string>(?:[^"]|\\")*)" of "(?P<tutorial_name_string>(?:[^"]|\\")*)" tutorial booking with "(?P<message_string>(?:[^"]|\\")*)" as a reason$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $username The username of the user to be removed.
     * @param string $slotname The name of the timeslot the user is to be removed from.
     * @param string $tutorialname The name of a tutorial booking.
     * @param string $message The message describing why the user was removed.
     * @return void
     */
    public function i_remove_user_from_slot_of_tutorial_booking_with_message_as_a_reason($username, $slotname, $tutorialname, $message) {
        global $DB;

        // Get the details of the user.
        $name = $DB->get_field('user', $DB->sql_concat_join("' '", array('firstname', 'lastname')), array('username' => $username));

        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array($name, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_field_to', array('message[text]', $message));
        $this->execute('behat_general::i_click_on', array(get_string('remove', 'mod_tutorialbooking'), 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Check that the user can sign up to any slot in the tutorial booking.
     *
     * @Then /^I should be able to sign up to "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $tutorialname The name of the tutorial booking activity.
     * @return void
     */
    public function i_should_be_able_to_sign_up_to_tutorial_booking($tutorialname) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_page_contains_text', array(get_string('signupforslot', 'mod_tutorialbooking')));
    }

    /**
     * Check that the user is signed up, but cannot see a link to remove themselves from a tutorial booking activity.
     *
     * @Given /^I should not be able to remove my sign up from "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $tutorialname The name of the tutorial booking activity.
     * @return void
     */
    public function i_should_not_be_able_to_remove_my_sign_up_from_tutorial_booking($tutorialname) {
        $signedupstring = get_string('you', 'mod_tutorialbooking');
        $removestring = get_string('removefromslot', 'mod_tutorialbooking');
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_contains_text', array($signedupstring, '.tutorial_sessions', 'css_element'));
        $this->execute('behat_general::assert_element_not_contains_text', array($removestring, '.tutorial_sessions', 'css_element'));
    }

    /**
     * The user should not be able to see the signup lists for the tutoral booking activity.
     *
     * @Given /^I should not be able to see signups on "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $tutorialname
     * @return void
     */
    public function i_should_not_be_able_to_see_signups_on_tutorial_booking($tutorialname) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::should_not_exist_in_the', array('.signedupuser', 'css_element', '.tutorial_sessions', 'css_element'));
    }

    /**
     * The user should not be able to sign up to the named slot in the names tutorial booking activity.
     *
     * @Given /^I should not be able to sign up to "(?P<slot_booking_name_string>(?:[^"]|\\")*)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $slotname The name of a tutorial booking slot to be checked.
     * @param string $tutorialname The name of the tutorial booking activity to be checked.
     * @return void
     */
    public function i_should_not_be_able_to_sign_up_to_slot_in_tutorial_booking($slotname, $tutorialname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $signupstring = get_string('signupforslot', 'mod_tutorialbooking');
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_not_contains_text', array($signupstring, $slotselector, 'xpath_element'));
    }

    /**
     * Check that the signup link does not appear on a tutorial booking.
     *
     * @Then /^I should not be able to sign up to "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $tutorialname The anme of the tutorial booking to be checked.
     * @return void
     */
    public function i_should_not_be_able_to_sign_up_to_tutorial_booking($tutorialname) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_page_not_contains_text', array(get_string('signupforslot', 'mod_tutorialbooking')));
    }

    /**
     * Check that a user by username is signed up to a specific slot in a tutorial booking.
     *
     * @Given /^I should see I am signed up to "(?P<slot_name_string>(?:[^"]|\\")*)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @global moodle_database $DB The Moodldatabase connection object.
     * @param string $slotname The nmae of the timeslot.
     * @param string $tutorialname The name of the tutorial.
     * @return void
     */
    public function i_should_see_i_am_signed_up_to_slot_in_tutorial_booking($slotname, $tutorialname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $signedupstring = get_string('you', 'mod_tutorialbooking');
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_contains_text', array($signedupstring, $slotselector, 'xpath_element'));
    }

    /**
     * Check that the editspaces error message is displayed correctly.
     *
     * @Then /^I should see I cannot reduce the spaces to "(?P<set_to_number>\d+)" or less than "(?P<totalsignups_number>\d+)"$/
     * @param int $setto The number of spaces the user tried to set.
     * @param int $numberofsignups The number of signups to the timeslot.
     * @return void
     */
    public function i_should_see_i_cannot_reduce_the_spaces_to_or_less_than($setto, $numberofsignups) {
        $errorstring = get_string('editspaceserror', 'mod_tutorialbooking', array('spaces' => (int)$setto, 'signedup' => (int)$numberofsignups));
        $this->execute('behat_general::assert_element_contains_text', array($errorstring, '#notice', 'css_element'));
    }

    /**
     * Check that a user by username is signed up to a specific slot in a tutorial booking.
     *
     * @Given /^I should see "(?P<user_name_string>(?:[^"]|\\")*)" is signed up to "(?P<slot_name_string>(?:[^"]|\\")*)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @global moodle_database $DB The Moodldatabase connection object.
     * @param string $username The username of the person to find.
     * @param string $slotname The nmae of the timeslot.
     * @param string $tutorialname The name of the tutorial.
     * @return void
     */
    public function i_should_see_is_signed_up_to_slot_in_tutorial_booking($username, $slotname, $tutorialname) {
        global $DB;
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        // Get the user's name from the database.
        $name = $DB->get_field('user', $DB->sql_concat_join("' '", array('firstname', 'lastname')), array('username' => $username));
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_contains_text', array($name, $slotselector, 'xpath_element'));
    }

    /**
     * Tests if a named slot is in the correct position of a named tutorial booking activity.
     *
     * @Given /^I should see "(?P<slot_name_string>(?:[^"]|\\")*)" in position "(?P<position_number>\d+)" of "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $slotname The name of the timeslot.
     * @param int $position The position of the timeslot, starting at 1.
     * @param string $tutorialname The name of the tutorial booking activity.
     * @return void
     */
    public function i_should_see_slot_in_position_of_tutorial_booking($slotname, $position, $tutorialname) {
        $slotselector = "//*[contains(concat(' ', normalize-space(@class), ' '), ' tutorial_session ') "
                . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' sectionname ')]]"
                . "[".(int)$position."]";
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_contains_text', array($this->escape($slotname), $slotselector, 'xpath_element'));
    }

    /**
     * For a named slot in a named tutorial booking, check that the slot has the
     * expected number of spaces and is oversubscribed by the given number of users.
     *
     * @Given /^I should see that "(?P<slot_name_string>(?:[^"]|\\")*)" with "(?P<totalspaces_number>\d+)" spaces is oversubscribed by "(?P<oversubscribed_number>\d+)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $slotname The anme of the timeslot to be checked.
     * @param int $totalspaces The spaces the timeslot is set to have.
     * @param int $oversubscribed The number of users subscribed above the total.
     * @param string $tutorialname The name of the tutorial booking activity to check.
     * @return void
     */
    public function i_should_see_that_slot_is_oversubscribed_in_tutorial_booking($slotname, $totalspaces, $oversubscribed, $tutorialname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $spaces = array(
            'total' => $totalspaces,
            'taken' => (int)$totalspaces + (int)$oversubscribed,
            'left' => $oversubscribed
        );
        $oversubscribedtext = get_string('numbersline_oversubscribed', 'mod_tutorialbooking', $spaces);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_contains_text', array($oversubscribedtext, $slotselector, 'xpath_element'));
    }

    /**
     * The user adds themselves to a named timeslot in a tutorial booking.
     *
     * @Given /^I sign up to "(?P<slot_name_string>(?:[^"]|\\")*)" in "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param string $slotname The name of the slot the user should add themselves to.
     * @param string $tutorialname The name of the tutorial booking activity.
     * @return void
     */
    public function i_sign_up_to_slot_in_tutorial_booking($slotname, $tutorialname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $signupstring = get_string('signupforslot', 'mod_tutorialbooking');
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array($signupstring, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Checks that the listed messages are visible in the named tutorial booking.
     *
     * @When /^I view sent messages in "(?P<tutorial_string>(?:[^"]|\\")*)" tutorial booking I should see:$/
     * @param string $tutorialname The name of the tutorial booking activity the messages should be in.
     * @param \Behat\Gherkin\Node\TableNode $messages The messages that should be present. The following parameters are valid:
     *              * message - The text of the message body (required)
     *              * sender - The username of the sender (optional)
     * @return void
     */
    public function i_view_sent_messages_in_tutorial_booking_i_should_see($tutorialname, TableNode $messages) {
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::click_link', array(get_string('viewmessages', 'mod_tutorialbooking')));

        foreach ($messages->getHash() as $message) {
            // XPath selector for a message.
            // The selector is looking for a .message element that
            // contains a .messagebody element with the text of the message.
            $messageselector = "//*[contains(concat(' ', normalize-space(@class), ' '), ' message ') "
                    . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' messagebody ') "
                    . "and (text()[contains(., '" .$this->escape($message['message']). "')]"
                    . "or .//*[text()[contains(., '" .$this->escape($message['message']). "')]])]]";
            $this->execute('behat_general::assert_element_contains_text', array($message['message'], $messageselector, 'xpath_element'));

            if (isset($message['sender'])) {
                // XPath selector for a sender in a message.
                // The selector looking for the .messageheader .text element that
                // is preceeded by a .messageheader .description with the localised
                // text of 'Sender'.
                $senderselector = $messageselector . "/descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' messageheader ') "
                        . "and .//*[contains(concat(' ', normalize-space(@class), ' '), ' description ') "
                        . "and text()[contains(., '" . get_string('sentby', 'mod_tutorialbooking') . "')]]]"
                        . "/descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' text ')]";
                $this->execute('behat_general::assert_element_contains_text', array($message['sender'], $senderselector, 'xpath_element'));
            }
        }
    }

    /**
     * Adds users to a named slot in a named tutorial booking, using the teachers force adding functionality.
     *
     * @Given /^in "(?P<slot_name_string>(?:[^"]|\\")*)" of "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking I add:$/
     * @global moodle_database $DB The Moodle database connection object.
     * @param string $slotname The name of the slot users should be added to.
     * @param string $tutorialname The name of the tutorial booking activity users should be added to.
     * @param \Behat\Gherkin\Node\TableNode $users The list of users that should be added to the tutorial booking.
     * @return void
     */
    public function in_slot_of_tutorial_booking_i_add($slotname, $tutorialname, TableNode $users) {
        global $DB;

        $slotselector = $this->generate_xpath_slot_selector($slotname);

        // Get the id's of the users we wish to add.
        $params = array();
        $insql = array();
        $identifier = 0;
        foreach ($users->getRows() as $user) {
            $insql[] = ':user'.$identifier;
            $params['user'.$identifier++] = $user[0];
        }
        // The users are identified by their user id on the form, so we can use that to select them.
        $insql = implode(',', $insql);
        $userids = $DB->get_fieldset_select('user', 'id', "username IN ($insql)", $params);

        $addstudents = get_string('addstudents', 'mod_tutorialbooking');
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::i_click_on_in_the', array($addstudents, 'link', $slotselector, 'xpath_element'));
        $this->execute('behat_forms::i_set_the_field_to', array('addtosession[]', implode(',', $userids)));
        $this->execute('behat_general::i_click_on', array($addstudents, 'button'));
        $this->execute('behat_general::i_wait_to_be_redirected', array());
    }

    /**
     * Test that the number of free space is reported correctly for a named timeslot on a named tutorial booking activity.
     *
     * @Given /^there should be "(?P<freespaces_number>\d+)" free space of "(?P<totalspaces_number>\d+)" total spaces available on "(?P<slot_name_string>(?:[^"]|\\")*)" of "(?P<tutorial_booking_name_string>(?:[^"]|\\")*)" tutorial booking$/
     * @param int $freespaces The number of free spaces expected.
     * @param int $totalspaces The total spaces on the timeslot.
     * @param string $slotname The name of the timeslot.
     * @param string $tutorialname The name of the tutorial booking activity.
     * @return void
     */
    public function there_should_be_free_space_of_total_spaces_available_on_slot_of_tutorial_booking($freespaces, $totalspaces, $slotname, $tutorialname) {
        $slotselector = $this->generate_xpath_slot_selector($slotname);
        $spaces = array(
            'total' => $totalspaces,
            'taken' => (int)$totalspaces - (int)$freespaces,
            'left' => $freespaces
        );
        $spacestext = get_string('numbersline', 'mod_tutorialbooking', $spaces);
        $this->execute('behat_general::click_link', array($this->escape($tutorialname)));
        $this->execute('behat_general::assert_element_contains_text', array($spacestext, $slotselector, 'xpath_element'));
    }
}
