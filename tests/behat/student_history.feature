# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria
Feature: View a student's assignment history (phase 4.2)
  In order to understand a student's tutoring history over time
  As an administrator
  I need to see every past and current assignment, with why it changed

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
      | tutor2   | Tutor     | Two      | tutor2@example.com   |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I press "Activate"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"

  Scenario: The history tab shows the current assignment
    Given I click on "View file" "link"
    When I click on "History" "link"
    Then I should see "Tutor One"
    And I should see "2026-2027"

  Scenario: Closing an assignment shows its reason in the history
    Given I click on "Close" "link"
    And I set the field "Reason for closing" to "End of support or co-tutoring"
    And I set the field "I confirm I want to close this assignment." to "1"
    And I press "Confirm closure"
    And I am on "local/monlaututoria/assignments/index.php"
    And I click on "View file" "link"
    When I click on "History" "link"
    Then I should see "End of support or co-tutoring"

  Scenario: Filtering the history by status hides closed rows
    Given I am on "local/monlaututoria/assignments/index.php"
    And I click on "View file" "link"
    And I click on "History" "link"
    When I set the field "status" to "Active"
    Then I should see "Tutor One"
