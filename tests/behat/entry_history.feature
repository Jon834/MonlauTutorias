# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording and the
# exact success/detail text against the real Moodle 5.1 instance.
@local @local_monlaututoria
Feature: Tutoring entry history and detail (phase 5.4)
  In order to review a student's past tutoring actuations
  As a tutor
  I need to see a filtered, paginated history and open the detail of a single entry

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                              | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents      | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createentry          | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:viewinternalnotes    | Allow      | monlaututoriatutor | System       |           |
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
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Tutoring reasons" in site administration
    And I press "New reason"
    And I set the field "Name" to "Seguimiento"
    And I set the field "Short name" to "seguimiento"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    And I log out
    And I am on "local/monlaututoria/entries/create.php?studentid=2&academicyearid=1" logged in as "tutor1"
    And I set the field "Motivo" to "Seguimiento"
    And I set the field "Comentario compartido con el alumno" to "Primera tutoria del curso"
    And I set the field "Nota interna" to "Nota solo para el equipo"
    And I press "Registrar tutoría"
    And I log out

  Scenario: A tutor sees the registered entry in the student's history and opens its detail
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    When I click on "Tutorías" "link"
    Then I should see "Tutor One"
    And I click on "Ver detalle" "link"
    Then I should see "Primera tutoria del curso"
    And I should see "Nota solo para el equipo"

  Scenario: A student never sees the internal note in their own history or detail, and the reasons column is hidden
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "student1"
    When I click on "Tutorías" "link"
    Then I should not see "Seguimiento"
    And I should not see "Nota solo para el equipo"
    And I should not see "Ver detalle"

  Scenario: Filtering the history by an unrelated reason returns no rows
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Tutoring reasons" in site administration
    And I press "New reason"
    And I set the field "Name" to "Convivencia"
    And I set the field "Short name" to "convivencia"
    And I press "Save changes"
    And I log out
    And I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    When I set the field "reasonid" to "Convivencia"
    Then I should see "No hay tutorías registradas para este curso académico con estos filtros."
