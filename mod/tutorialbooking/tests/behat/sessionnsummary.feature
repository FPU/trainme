@mod @mod_tutorialbooking @uon
Feature: Time slots should allow me to enter some descriptive text.
    In order convey information about the slot to students
    As a teacher
    I need to be able to add well formatted information to a time slot.

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | student1 | Student   | 1        | student1@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
        And the following "activities" exist:
            | activity        | course | idnumber | name             | intro                           |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking | This is a test tutorial booking |

    Scenario: Teacher should be able to add summary text that a student can see
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 1 |
            | Details | Hello world. |
            | Max Number of Students | 1 |
            | Position | Top of the Page |
        Then I should see "Slot 1"
        And I should see "Hello world."
        When I log out
        And I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Tutorial booking"
        Then I should see "Slot 1"
        And I should see "Hello world."
