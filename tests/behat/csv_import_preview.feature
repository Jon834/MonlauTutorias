# Written against current Moodle Behat step conventions but not executed in
# this environment (no Behat instance available here). PHPUnit for the
# underlying service logic already passed manual review; this only covers the
# browser-level flow. NOTE: the file-upload step and the fixture path
# resolution (tests/fixtures/sample_import.csv) are the least certain part of
# this feature — verify against the real Moodle 5.1 instance, and adjust the
# "I upload ... file to ... filepicker" step wording if it does not match.
@local @local_monlaututoria
Feature: Preview a CSV assignment import
  In order to bulk-import tutor-student assignments
  As an administrator
  I need to upload a CSV file and see a preview before anything is applied

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
      | teacher1 | Teacher   | One      | teacher1@example.com  |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"

  Scenario: Administrator uploads a valid CSV and sees a preview
    When I navigate to "Plugins > Local plugins > Monlau Tutoria > Import assignments from CSV" in site administration
    And I upload "local/monlaututoria/tests/fixtures/sample_import.csv" file to "CSV file" filepicker
    And I press "Preview"
    Then I should see "Preview summary"
    And I should see "Rows analysed: 1"
    And I should see "Valid: 1"
    And I should see "Recalculate preview"
    And I should see "Applying this import is not available yet"

  Scenario: Excluding a row and recalculating marks it as excluded
    Given I navigate to "Plugins > Local plugins > Monlau Tutoria > Import assignments from CSV" in site administration
    And I upload "local/monlaututoria/tests/fixtures/sample_import.csv" file to "CSV file" filepicker
    And I press "Preview"
    When I set the field "Exclude row 2" to "1"
    And I press "Recalculate preview"
    Then I should see "Excluded: 1"

  Scenario: A user without capability cannot access the import page
    When I am on "local/monlaututoria/assignments/import.php" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"
