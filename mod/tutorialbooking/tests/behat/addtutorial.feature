@mod @mod_tutorialbooking @uon
Feature: Add a tutorial booking activity to a course and add some slots
    In order to allow students to signup to tutorials
    As a teacher
    I need to be able to add tutorial booking activities to a course.

    Scenario: Add a tutorial booking activity and create two signup slots.
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
        And I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And I add a "Tutorial Booking" to section "1" and I fill the form with:
            | Sign-up List Title | Tutorial booking |
            | Sign-up List Notes | This is a tutorial booking form |
            | Locked | No |
            | Privacy | Students can see all signups |
            | Availability | Show on course page |
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 1 |
            | Max Number of Students | 10 |
            | Position | Top of the Page |
        And I add a new timeslot to "Tutorial booking" tutorial booking with:
            | Title | Slot 2 |
            | Max Number of Students | 5 |
            | Position | Bottom of the Page |
        And I log out
        And I log in as "student1"
        And I follow "Course 1"
        When I follow "Tutorial booking"
        Then I should see "Slot 1"
        And I should see "Slot 2"
        And I should see "Sign me up for this slot"
