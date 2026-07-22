# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria
Feature: Access control for academic years
  In order to protect institutional tutoring data
  As a user without the required capability
  I should not be able to access the administration pages

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |

  Scenario: A user without capability cannot access the academic years page
    When I am on "local/monlaututoria/academicyears.php" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"

  Scenario: A user without capability cannot access the reasons page
    When I am on "local/monlaututoria/reasons.php" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"
