# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance. PHPUnit for the underlying service/repository
# logic already passed manual review; this only covers the browser-level flow.
@local @local_monlaututoria
Feature: Close a tutor-student assignment
  In order to keep tutoring assignments up to date
  As an administrator
  I need to close an active assignment from the administration pages

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

  Scenario: Administrator closes a non-primary assignment
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Type" to "Support"
    And I press "New assignment"
    When I click on "Close" "link"
    And I set the field "Reason for closing" to "End of support or co-tutoring"
    And I set the field "I confirm I want to close this assignment." to "1"
    And I press "Confirm closure"
    Then I should see "Assignment closed."
    And I should not see "Close" in the ".btn-outline-danger" "css_element"

  Scenario: Closing the active primary tutor shows a warning and the no-primary message
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    When I click on "Close" "link"
    Then I should see "Closing this assignment will leave the student without an active primary tutor."
    When I set the field "Reason for closing" to "Tutor left"
    And I set the field "I confirm I want to close this assignment." to "1"
    And I press "Confirm closure"
    Then I should see "The student is now left without an active primary tutor."

  Scenario: An assignment cannot be closed twice
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"
    And I click on "Close" "link"
    And I set the field "Reason for closing" to "Administrative error"
    And I set the field "I confirm I want to close this assignment." to "1"
    And I press "Confirm closure"
    When I am on "local/monlaututoria/assignments/close.php?id=1"
    Then I should see "Sorry, the following error was detected"

  Scenario: A user without capability cannot access the close page
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"
    When I am on "local/monlaututoria/assignments/close.php?id=1" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"
