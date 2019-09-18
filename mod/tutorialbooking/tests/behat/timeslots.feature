@mod @mod_tutorialbooking @uon
Feature: Time slots have settings that should be editable.
    In order to have meaning time slots must be able to have editable properties
    As a teacher
    I need to be able to change the settings of time slots.

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | student1 | Student   | 1        | student1@example.com |
            | student2 | Student   | 2        | student2@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
            | student2 | C1     | student        |
        And the following "activities" exist:
            | activity        | course | idnumber | name             | intro                           |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking |
        And I log in as "teacher1"
        And I am on "Course 1" course homepage
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 1 |
            | Max Number of Students | 1 |
            | Position | Top of the Page |
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 2 |
            | Max Number of Students | 2 |
            | Position | Bottom of the Page |
        And I log out

    Scenario: I should be able to create a time slot based on an existing slot
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And I should see "Slot 1" in position "1" of "Tutorial booking" tutorial booking
        And I should see "Slot 2" in position "2" of "Tutorial booking" tutorial booking
        And I create new slot based on "Slot 1" in "Tutorial booking" tutorial booking with:
            | Title | New slot |
            | Position | Top of the Page |
        Then I should see "New slot" in position "1" of "Tutorial booking" tutorial booking
        And there should be "1" free space of "1" total spaces available on "New slot" of "Tutorial booking" tutorial booking
        And I should see "Slot 1" in position "2" of "Tutorial booking" tutorial booking
        And I should see "Slot 2" in position "3" of "Tutorial booking" tutorial booking

    Scenario: I should be able to change the details of a time slot.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And I should see "Slot 1" in position "1" of "Tutorial booking" tutorial booking
        And I should see "Slot 2" in position "2" of "Tutorial booking" tutorial booking
        And there should be "1" free space of "1" total spaces available on "Slot 1" of "Tutorial booking" tutorial booking
        When I edit "Slot 1" in "Tutorial booking" tutorial booking with:
            | Title | Modified slot 1 |
            | Max Number of Students | 4 |
            | Position | Bottom of the Page |
        Then I should see "Slot 2" in position "1" of "Tutorial booking" tutorial booking
        And I should see "Modified slot 1" in position "2" of "Tutorial booking" tutorial booking
        And there should be "4" free space of "4" total spaces available on "Modified slot 1" of "Tutorial booking" tutorial booking

    Scenario: I should be able to delete a time slot.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And I should see "Slot 1" in position "1" of "Tutorial booking" tutorial booking
        And I should see "Slot 2" in position "2" of "Tutorial booking" tutorial booking
        When I delete "Slot 1" of "Tutorial booking" tutorial booking
        Then I should see "Slot 2" in position "1" of "Tutorial booking" tutorial booking

    Scenario: It should not be possible to set the number of spaces in a timeslot to less than 1 or more than 30000.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 0 |
        Then there should be "1" free space of "1" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 30001 |
        Then there should be "30000" free space of "30000" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 2 |
        Then there should be "2" free space of "2" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 30000 |
        Then there should be "30000" free space of "30000" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | -1 |
        Then there should be "1" free space of "1" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 29999 |
        Then there should be "29999" free space of "29999" total spaces available on "Slot 2" of "Tutorial booking" tutorial booking

    Scenario: An editing teacher should be able to edit the number of slots to be lower than the number of signups.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And in "Slot 2" of "Tutorial booking" tutorial booking I add:
            | student1 |
            | student2 |
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 1 |
        Then I should see that "Slot 2" with "1" spaces is oversubscribed by "1" in "Tutorial booking" tutorial booking

    Scenario: A user without the capability to oversubscribe should not be able to edit the number of slots to be lower than the number of signups.
        Given the following "permission overrides" exist:
            | capability                        | permission | role           | contextlevel | reference |
            | mod/tutorialbooking:oversubscribe | Prevent    | editingteacher | Course       | C1        |
        And I log in as "teacher1"
        And I am on "Course 1" course homepage
        And in "Slot 2" of "Tutorial booking" tutorial booking I add:
            | student1 |
            | student2 |
        When I edit "Slot 2" in "Tutorial booking" tutorial booking with:
            | Max Number of Students | 1 |
        Then I should see I cannot reduce the spaces to "1" or less than "2"
