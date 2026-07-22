# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria
Feature: Manage tutoring reasons
  In order to categorise tutoring sessions
  As an administrator
  I need to create, edit, activate and reorder reasons

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Tutoring reasons" in site administration

  Scenario: Administrator sees the seeded reasons
    Then I should see "Initial welcome"
    And I should see "Other"

  Scenario: Administrator creates a new reason
    When I press "New reason"
    And I set the field "Name" to "Custom reason"
    And I set the field "Short name" to "customreason"
    And I press "Save changes"
    Then I should see "Custom reason"

  Scenario: Administrator deactivates a reason
    When I click on "Deactivate" "link" in the "Other" "table_row"
    Then I should see "Activate" in the "Other" "table_row"
