@mod @mod_tutorialbooking @uon
Feature: Teachers can remove students from timeslots.
    In order to effectively manage timeslots
    As a teacher
    I need to be able to remove a student from a timeslot

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | student1 | Student   | 1        | student1@example.com |
            | student2 | Student   | 2        | student2@example.com |
            | student3 | Student   | 3        | student3@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
            | student2 | C1     | student        |
            | student3 | C1     | student        |
        And the following "activities" exist:
            | activity        | course | idnumber | name             | intro                           |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking |
        And I log in as "teacher1"
        And I am on "Course 1" course homepage
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 1 |
            | Max Number of Students | 2 |
            | Position | Top of the Page |
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 2 |
            | Max Number of Students | 1 |
            | Position | Bottom of the Page |
        And in "Slot 1" of "Tutorial booking" tutorial booking I add:
            | student1 |
            | student3 |
        And in "Slot 2" of "Tutorial booking" tutorial booking I add:
            | student2 |
        And I log out

    Scenario: As a teacher I can remove a student from a slot, the student should see the explanation for the removal.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        # Remove a student, and check only they are removed.
        When I remove "student1" from "Slot 1" of "Tutorial booking" tutorial booking with "I am removing you" as a reason
        Then there should be "1" free space of "2" total spaces available on "Slot 1" of "Tutorial booking" tutorial booking
        And there should be "0" free space of "1" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        And I should see "student3" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        # Remove a second student.
        When I remove "student2" from "Slot 2" of "Tutorial booking" tutorial booking with "I just feel like it" as a reason
        Then there should be "1" free space of "2" total spaces available on "Slot 1" of "Tutorial booking" tutorial booking
        And there should be "1" free space of "1" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        And I should see "student3" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        # Check that the teacher can see the removal messages in the history.
        When I view sent messages in "Tutorial Booking" tutorial booking I should see:
            | message             | sender   |
            | I am removing you   | teacher1 |
            | I just feel like it | teacher1 |
        And I log out
        # Check that the first removed student can sign up to slots and see the message.
        When I log in as "student1"
        And I am on "Course 1" course homepage
        Then I should be able to sign up to "Tutorial booking" tutorial booking
        And I log out
        When I log in as "student3"
        And I am on "Course 1" course homepage
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I log out
        When I log in as "student2"
        And I am on "Course 1" course homepage
        Then I should be able to sign up to "Tutorial booking" tutorial booking

    Scenario: As a teacher if I cancel the remove process a student should not be removed from the slot.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        When I cancel the removal of "student1" from "Slot 1" of "Tutorial booking" tutorial booking with "I am removing you" as a reason
        Then there should be "0" free space of "2" total spaces available on "Slot 1" of "Tutorial booking" tutorial booking
        And I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
