# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance. NOTE: "editing a closed assignment without
# manageclosedassignments" and "editing a closed assignment without a reason"
# are deliberately NOT covered here — there is no browser flow to close an
# assignment yet (that is phase 3B.3); both rules are already covered by
# tests/service/assignment_service_test.php instead.
@local @local_monlaututoria
Feature: Edit a tutor-student assignment
  In order to correct assignment data
  As an administrator
  I need to edit an existing assignment from the administration pages

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"

  Scenario: Administrator edits an assignment successfully
    When I click on "Edit" "link"
    And I set the field "Administrative note" to "Seguimiento inicial"
    And I press "Save changes"
    Then I should see "Assignment updated."

  Scenario: A user without capability cannot access the edit page
    When I am on "local/monlaututoria/assignments/edit.php?id=1" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"
