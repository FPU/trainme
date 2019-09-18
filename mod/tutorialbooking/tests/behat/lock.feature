@mod @mod_tutorialbooking @uon
Feature: A tutorial booking can be locked to prevent students from modifying signups
    In order to finalise signups
    As a teacher
    I should be able to lock a tutorial

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
            | activity        | course | idnumber | name             | intro                           | locked |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking | 1      |
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

    Scenario: A student cannot sign up to a slot while a tutorial booking is locked.
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        Then I should not be able to sign up to "Tutorial booking" tutorial booking

    Scenario: A student cannot remove themselves from a slot while a tutorial booking is locked.
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And in "Slot 1" of "Tutorial booking" tutorial booking I add:
            | student1 |
        And I log out
        Then I log in as "student1"
        And I am on "Course 1" course homepage
        And I should see I am signed up to "Slot 1" in "Tutorial booking" tutorial booking
        But I should not be able to remove my sign up from "Tutorial booking" tutorial booking
