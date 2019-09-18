@mod @mod_tutorialbooking @uon
Feature: Delete tutorial booking activities
    In order to remove tutorial booking activities
    As a teacher
    I need to be able to delete tutorial booking activities from a course.

    Scenario: Delete a tutorial booking.
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
            | activity        | course | idnumber | name                         | intro                           |
            | tutorialbooking | C1     | tuorial1 | Tutorial booking to delete   | This is a test tutorial booking |
            | tutorialbooking | C1     | tuorial2 | Tutorial booking 2           | This is a test tutorial booking |
        When I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And "Tutorial booking to delete" activity should be visible
        And "Tutorial booking 2" activity should be visible
        And I delete "Tutorial booking to delete" activity
        Then "Tutorial booking 2" activity should be visible
        And I turn editing mode off
        And I should not see "Tutorial booking to delete"
