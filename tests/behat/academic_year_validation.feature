# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists. The exact field
# names for the date_selector sub-fields (day/month/year) must be confirmed
# against the rendered form.
@local @local_monlaututoria
Feature: Academic year date validation
  In order to keep academic year data consistent
  As an administrator
  I should not be able to save an academic year with an end date before its start date

  Scenario: End date before start date is rejected
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    When I set the field "Name" to "Invalid year"
    And I set the field "Short name" to "invalidyear"
    And I set the following fields to these values:
      | startday   | 1    |
      | startmonth | September |
      | startyear  | 2027 |
      | endday     | 1    |
      | endmonth   | September |
      | endyear    | 2026 |
    And I press "Save changes"
    Then I should see "The end date must be after the start date"
