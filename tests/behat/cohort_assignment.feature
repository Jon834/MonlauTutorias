# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording — in
# particular the "cohorts"/"cohort members" generator steps and the exact
# success text — against the real Moodle 5.1 instance.
@local @local_monlaututoria @local_monlaututoria_advanced
Feature: Cohort-based bulk assignment (the "confirm" step of phase 3C)
  In order to assign a tutor to a whole group of students at once
  As a coordinator
  I need to preview what a cohort sync would do before it writes anything, then confirm it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | student2 | Student   | Two      | student2@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
      | tutor2   | Tutor     | Two      | tutor2@example.com    |
    And the following "cohorts" exist:
      | name    | idnumber |
      | Group A | groupa   |
    And the following "cohort members" exist:
      | cohort   | user     |
      | groupa   | student1 |
      | groupa   | student2 |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                                    | permission | role               | contextlevel | reference |
      | local/monlaututoria:managecohortassignments   | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:assignstudents            | Allow      | monlaututoriatutor | System       |           |
    And the following "role assigns" exist:
      | user   | role               | contextlevel | reference |
      | tutor1 | monlaututoriatutor | System       |           |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I press "Activate"
    And I log out

  Scenario: A coordinator previews and confirms a cohort assignment, creating a tutoring assignment for every member
    Given I am on "local/monlaututoria/assignments/cohort_create.php" logged in as "tutor1"
    And I set the field "Cohorte" to "Group A"
    And I set the field "Curso académico" to "2026-2027"
    And I set the field "Tutor principal" to "Tutor One"
    And I set the field "Modo de sincronización" to "Añadir asignaciones"
    When I press "Previsualizar"
    Then I should see "Alumnos analizados: 2"
    And I should see "Asignaciones nuevas: 2"
    And I press "Aplicar"
    Then I should see "Debes confirmar que quieres aplicar esta asignación por cohorte."
    When I set the field "Confirmo que quiero aplicar esta asignación por cohorte." to "1"
    And I press "Aplicar"
    Then I should see "Asignación por cohorte aplicada correctamente."
    And I should see "Asignaciones creadas: 2"
    And I am on "local/monlaututoria/assignments/index.php"
    And I should see "Student One"
    And I should see "Student Two"

  Scenario: A "preview only" operation cannot be confirmed
    Given I am on "local/monlaututoria/assignments/cohort_create.php" logged in as "tutor1"
    And I set the field "Cohorte" to "Group A"
    And I set the field "Curso académico" to "2026-2027"
    And I set the field "Tutor principal" to "Tutor One"
    And I set the field "Modo de sincronización" to "Solo previsualizar"
    When I press "Previsualizar"
    Then I should see "Alumnos analizados: 2"
    And I should not see "Aplicar"

  Scenario: A user without managecohortassignments cannot reach the cohort assignment page
    When I am on "local/monlaututoria/assignments/cohort_create.php" logged in as "tutor2"
    Then I should see "Sorry, but you do not currently have permission to do that"
