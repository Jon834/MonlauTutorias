# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria
Feature: Create an academic year
  In order to register a new academic year
  As an administrator
  I need to create it from the administration pages

  Scenario: Administrator creates a new academic year
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    When I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    Then I should see "2026-2027"
