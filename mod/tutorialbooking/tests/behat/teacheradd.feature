@mod @mod_tutorialbooking @uon
Feature: Teachers can add students to timeslots.
    In order to assign students to a specific timeslot
    As a teacher
    I need to be able to add a student to a timeslot

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email            |
            | teacher1 | Teacher   | 1        | teacher1@example.com |
            | teacher2 | Teacher   | 2        | teacher2@example.com |
            | student1 | Student   | 1        | student1@example.com |
            | student2 | Student   | 2        | student2@example.com |
        And the following "courses" exist:
            | fullname | shortname | category |
            | Course 1 | C1        | 0        |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | teacher2 | C1     | teacher        |
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
            | Max Number of Students | 1 |
            | Position | Bottom of the Page |
        And I log out

    Scenario: Teachers should be able to add a student to a timeslot
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And in "Slot 1" of "Tutorial booking" tutorial booking I add:
            | student1 |
        Then I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        When I log out
        And I log in as "student1"
        And I am on "Course 1" course homepage
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 1" in "Tutorial booking" tutorial booking

    Scenario: Editing teachers should be able to add a student to a full timeslot
        Given I log in as "teacher1"
        And I am on "Course 1" course homepage
        And in "Slot 1" of "Tutorial booking" tutorial booking I add:
            | student1 |
            | student2 |
        Then I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        And I should see "student2" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        And I should see that "Slot 1" with "1" spaces is oversubscribed by "1" in "Tutorial booking" tutorial booking
        When I log out
        And I log in as "student1"
        And I am on "Course 1" course homepage
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 1" in "Tutorial booking" tutorial booking
        And I should see "student2" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
        When I log out
        And I log in as "student2"
        And I am on "Course 1" course homepage
        Then I should not be able to sign up to "Tutorial booking" tutorial booking
        And I should see I am signed up to "Slot 1" in "Tutorial booking" tutorial booking
        And I should see "student1" is signed up to "Slot 1" in "Tutorial booking" tutorial booking
