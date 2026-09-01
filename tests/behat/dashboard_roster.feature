# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria
Feature: The tutor dashboard "Mis alumnos" roster (phase 13)
  In order to recognise my tutees at a glance
  As a tutor
  I want a card grid with their photos and names that links to each file

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
      | tutor2   | Tutor     | Two      | tutor2@example.com   |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                          | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:viewstudent     | Allow      | monlaututoriatutor | System       |           |
    And the following "role assigns" exist:
      | user   | role               | contextlevel | reference |
      | tutor1 | monlaututoriatutor | System       |           |
      | tutor2 | monlaututoriatutor | System       |           |
    And the following config values are set as admin:
      | simplemode | 1 | local_monlaututoria |
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
    And I press "New assignment"
    And I log out

  Scenario: The roster shows the tutor's own student and links to their file
    Given I am on "local/monlaututoria/dashboard.php" logged in as "tutor1"
    Then I should see "Mis alumnos"
    And I should see "Student One"
    And I should see "Sin tutoría aún"
    When I click on "Student One" "link"
    Then I should see "2026-2027"

  Scenario: The roster does not show a student assigned to a different tutor
    When I am on "local/monlaututoria/dashboard.php" logged in as "tutor2"
    Then I should not see "Student One"

  Scenario: The Pendientes view is still reachable from the switch
    Given I am on "local/monlaututoria/dashboard.php" logged in as "tutor1"
    When I click on "Pendientes" "link"
    Then I should see "Student One"
