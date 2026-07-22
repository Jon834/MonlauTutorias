# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria
Feature: View academic years
  In order to know which academic years exist
  As an administrator
  I need to see the list of academic years

  Scenario: Administrator views the empty academic years list
    Given I log in as "admin"
    When I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    Then I should see "Academic years"
    And I should see "There is no active academic year"
