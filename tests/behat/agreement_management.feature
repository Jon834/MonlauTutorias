# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording, and in
# particular the assumed entry id (1, the first tutoring entry created in the
# Background) — against the real Moodle 5.1 instance, same disclosed caveat
# already applied to assignment ids elsewhere in this project.
@local @local_monlaututoria
Feature: Agreements (phase 6.1/6.3)
  In order to turn a tutoring entry into a concrete, trackable action
  As a tutor
  I need to create an agreement with a responsible party and a due date, and mark it completed

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                             | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents     | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:viewstudent         | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createentry         | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createagreement     | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:manageagreements    | Allow      | monlaututoriatutor | System       |           |
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
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"
    And I log out
    And I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I press "Registrar tutoría"
    And I set the field "Comentario compartido con el alumno" to "Seguimiento"
    And I press "Registrar tutoría"
    And I log out

  Scenario: A tutor creates an agreement and marks it completed
    Given I am on "local/monlaututoria/entries/view.php?id=1" logged in as "tutor1"
    When I click on "Crear acuerdo" "link"
    And I set the field "Description" to "Attend the weekly review"
    And I set the field "Due date [day]" to "1"
    And I press "Crear acuerdo"
    Then I should see "Acuerdo creado correctamente."
    When I click on "Mark completed" "link"
    And I press "Continue"
    Then I should see "Agreement updated successfully."

  Scenario: A user without createagreement cannot reach the creation page
    When I am on "local/monlaututoria/agreements/create.php?entryid=1" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"

  Scenario: A tutor with createagreement but no relationship to a different student is denied (IDOR)
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | tutor2   | Tutor     | Two      | tutor2b@example.com   |
    And the following "role assigns" exist:
      | user   | role               | contextlevel | reference |
      | tutor2 | monlaututoriatutor | System       |           |
    When I am on "local/monlaututoria/agreements/create.php?entryid=1" logged in as "tutor2"
    Then I should see "You do not have access to this student's tutoring data."
