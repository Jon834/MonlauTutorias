# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording — in
# particular the "roles"/"permission overrides"/"role assigns" generator steps
# and the exact error/success text — against the real Moodle 5.1 instance.
@local @local_monlaututoria
Feature: Quick tutoring entry registration (phase 5.2)
  In order to record a tutoring actuation in under a minute
  As a tutor
  I need to register an entry for a student already preselected, without picking the student or myself as tutor

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | student2 | Student   | Two      | student2@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
      | tutor2   | Tutor     | Two      | tutor2@example.com    |
      | teacher1 | Teacher   | One      | teacher1@example.com  |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                           | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents   | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createentry       | Allow      | monlaututoriatutor | System       |           |
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
    And I press "Activate"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Tutoring reasons" in site administration
    And I press "New reason"
    And I set the field "Name" to "Seguimiento"
    And I set the field "Short name" to "seguimiento"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Contact modalities" in site administration
    And I press "New modality"
    And I set the field "Name" to "Presencial"
    And I set the field "Short name" to "presencial"
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

  Scenario: A tutor registers a quick entry for their own assigned student
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    When I click on "Tutorías" "link"
    And I press "Registrar tutoría"
    And I set the field "Modalidad" to "Presencial"
    And I set the field "Motivo" to "Seguimiento"
    And I set the field "Comentario compartido con el alumno" to "Buen progreso este trimestre"
    And I press "Registrar tutoría"
    Then I should see "Tutoría registrada correctamente."

  Scenario: A user without createentry cannot reach the registration page
    When I am on "local/monlaututoria/entries/create.php?studentid=2&academicyearid=1" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"

  Scenario: A tutor with createentry but no relationship to a different student is denied (IDOR)
    When I am on "local/monlaututoria/entries/create.php?studentid=3&academicyearid=1" logged in as "tutor1"
    Then I should see "You do not have access to this student's tutoring data."

  Scenario: The shared comment is required
    Given I am on "local/monlaututoria/entries/create.php?studentid=2&academicyearid=1" logged in as "tutor1"
    And I set the field "Motivo" to "Seguimiento"
    When I press "Registrar tutoría"
    Then I should see "Required"
