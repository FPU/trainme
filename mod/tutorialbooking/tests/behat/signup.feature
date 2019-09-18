@mod @mod_tutorialbooking @uon
Feature: Students can signup and remove themselves from tutorial booking slots.
    In order to book onto tutorials
    As a student
    I need to be able to signup to, and remove myself from, time slots.

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

    Scenario: A student can sign up to a slot and then remove themselves from it.
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        When I sign up to "Slot 2" in "Tutorial booking" tutorial booking
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 2" in "Tutorial booking" tutorial booking
        When I remove my sign up from "Tutorial booking" tutorial booking
        Then I should be able to sign up to "Tutorial booking" tutorial booking

    Scenario: A student cannot sign up to a full slot, but can sign up to, and remove themselves from another slot.
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        And I sign up to "Slot 1" in "Tutorial booking" tutorial booking
        And I log out
        And I log in as "student2"
        And I am on "Course 1" course homepage
        Then I should be able to sign up to "Tutorial booking" tutorial booking
        But I should not be able to sign up to "Slot 1" in "Tutorial booking" tutorial booking
        And I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        When I sign up to "Slot 2" in "Tutorial booking" tutorial booking
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 2" in "Tutorial booking" tutorial booking
        And I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        When I remove my sign up from "Tutorial booking" tutorial booking
        Then I should be able to sign up to "Tutorial booking" tutorial booking
        And I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking

    Scenario: A student should be able to cancel removing their signup
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        When I sign up to "Slot 2" in "Tutorial booking" tutorial booking
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 2" in "Tutorial booking" tutorial booking
        When I cancel remove my sign up from "Tutorial booking" tutorial booking
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 2" in "Tutorial booking" tutorial booking
