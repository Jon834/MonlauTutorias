# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria
Feature: Activate an academic year
  In order to work within the correct academic year
  As an administrator
  I need to activate one, deactivating any previously active year

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2025-2026"
    And I set the field "Short name" to "2025-2026"
    And I press "Save changes"
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"

  Scenario: Activating a first academic year needs no warning about a previous one
    When I click on "Activate" "link" in the "2025-2026" "table_row"
    Then I should see "Activate this academic year?"
    And I press "Continue"
    And I should see "Academic year activated"

  Scenario: Activating a second academic year warns about the currently active one
    Given I click on "Activate" "link" in the "2025-2026" "table_row"
    And I press "Continue"
    When I click on "Activate" "link" in the "2026-2027" "table_row"
    Then I should see "2025-2026"
    And I press "Continue"
    And I should see "Academic year activated"
