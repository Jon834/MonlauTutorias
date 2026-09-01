# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording — in
# particular "the following config values are set as admin" and the exact
# error text — against the real Moodle 5.1 instance.
@local @local_monlaututoria @local_monlaututoria_simplemode
Feature: Simple mode hides the advanced modules (phase 13)
  In order to keep the plugin practical for a first pilot
  As an administrator
  I want a single switch that hides agreements, follow-ups, referrals,
  coordination, notifications and imports without deleting anything

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                            | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents   | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:managereferrals   | Allow      | monlaututoriatutor | System       |           |
    And the following "role assigns" exist:
      | user   | role               | contextlevel | reference |
      | tutor1 | monlaututoriatutor | System       |           |

  Scenario: With simple mode off the referrals page and nav entry are available
    Given the following config values are set as admin:
      | simplemode | 0 | local_monlaututoria |
    When I am on "local/monlaututoria/dashboard.php" logged in as "tutor1"
    Then I should see "Derivaciones"
    And I am on "local/monlaututoria/referrals/index.php" logged in as "tutor1"
    And I should not see "el sitio está en modo simple"

  Scenario: With simple mode on the referrals nav entry is gone
    Given the following config values are set as admin:
      | simplemode | 1 | local_monlaututoria |
    When I am on "local/monlaututoria/dashboard.php" logged in as "tutor1"
    Then I should not see "Derivaciones"

  Scenario: With simple mode on the referrals page refuses to load
    Given the following config values are set as admin:
      | simplemode | 1 | local_monlaututoria |
    When I am on "local/monlaututoria/referrals/index.php" logged in as "tutor1"
    Then I should see "el sitio está en modo simple"

  Scenario: With simple mode on the coordination and notifications pages also refuse to load
    Given the following config values are set as admin:
      | simplemode | 1 | local_monlaututoria |
    When I am on "local/monlaututoria/coordination.php" logged in as "tutor1"
    Then I should see "el sitio está en modo simple"
    And I am on "local/monlaututoria/notifications.php" logged in as "tutor1"
    And I should see "el sitio está en modo simple"
