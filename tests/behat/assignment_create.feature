# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording — in
# particular how "I set the field" interacts with the autocomplete student/tutor
# fields — against the real Moodle 5.1 instance. PHPUnit for the underlying
# service/repository logic already passed manual review; this only covers the
# browser-level flow.
@local @local_monlaututoria
Feature: Create a tutor-student assignment manually
  In order to register who tutors a student
  As an administrator
  I need to create an assignment from the administration pages

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration

  Scenario: Administrator creates a valid manual assignment
    When I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"
    Then I should see "Assignment created."

  Scenario: Invalid dates show a validation error
    When I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the following fields to these values:
      | timeend[enabled] | 1 |
    And I press "New assignment"
    Then I should see "The end date cannot be before the start date."

  Scenario: A duplicate primary tutor shows a validation error
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    When I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    Then I should see "This student already has an active primary tutor for this academic year."

  Scenario: A user without capability cannot access the creation page
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    When I am on "local/monlaututoria/assignments/create.php" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"
