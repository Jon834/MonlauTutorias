# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording — in
# particular the "roles"/"permission overrides"/"role assigns" generator steps
# and the exact error text — against the real Moodle 5.1 instance.
#
# Phase 3E.2 ("Pruebas IDOR ... y manipulación de parámetros" del trabajo
# obligatorio de la Fase 3E). tests/service/scope_service_test.php already
# covers can_user_access_student() in isolation; this feature proves the same
# denial actually happens end to end through the real pages, for a user who
# DOES hold the page's capability but lacks scope over this specific student
# — the scenario a capability-only check can never catch.
@local @local_monlaututoria
Feature: A tutor cannot access another tutor's student data (IDOR)
  In order to keep tutoring data private to the assigned tutor
  As a tutor without global visibility
  I need to be denied when I try to reach another tutor's student by guessing or editing an id/parameter

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | student2 | Student   | Two      | student2@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
      | tutor2   | Tutor     | Two      | tutor2@example.com    |
    And the following "roles" exist:
      | name                | shortname          |
      | Monlau Tutor        | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                                   | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents           | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:viewstudent                | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:manageassignments          | Allow      | monlaututoriatutor | System       |           |
    And the following "role assigns" exist:
      | user   | role               | contextlevel | reference |
      | tutor1 | monlaututoriatutor | System       |           |
      | tutor2 | monlaututoriatutor | System       |           |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student Two"
    And I set the field "Tutor" to "Tutor Two"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    And I log out

  Scenario: A tutor cannot view another tutor's student's assignment by id
    When I am on "local/monlaututoria/assignments/view.php?id=2" logged in as "tutor1"
    Then I should see "You do not have access to this student's tutoring data."

  Scenario: A tutor cannot edit another tutor's student's assignment by id
    When I am on "local/monlaututoria/assignments/edit.php?id=2" logged in as "tutor1"
    Then I should see "You do not have access to this student's tutoring data."

  Scenario: A tutor cannot close another tutor's student's assignment by id
    When I am on "local/monlaututoria/assignments/close.php?id=2" logged in as "tutor1"
    Then I should see "You do not have access to this student's tutoring data."

  Scenario: A tutor's listing ignores a tampered tutorid parameter
    # 999999 is a deliberately fabricated id, not any real user's — this is
    # the point: assignments/index.php must ignore whatever tutorid a
    # request supplies and force it to the logged-in user's own id when the
    # viewer lacks viewallassignments. If that override were broken, the
    # query would filter by tutorid=999999 (matching nobody) and the list
    # would come back empty — "Student One" would NOT appear either way,
    # which is exactly what distinguishes a working override from a broken one.
    When I am on "local/monlaututoria/assignments/index.php?tutorid=999999" logged in as "tutor1"
    Then I should see "Student One"
