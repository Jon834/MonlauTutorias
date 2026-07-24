# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording and the
# exact success text against the real Moodle 5.1 instance.
#
# Not covered here (needs manipulating wall-clock time, impractical in Behat):
# editing outside the configurable window requiring a reason —
# entry_service_test.php already covers this directly (phase 5.5).
@local @local_monlaututoria
Feature: Editing and annulling a tutoring entry (phase 5.5)
  In order to correct or retire a tutoring entry without losing its history
  As a tutor
  I need to edit an entry I authored, or annul one, and see that the row is never actually deleted

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
      | tutor2   | Tutor     | Two      | tutor2@example.com    |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                              | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents      | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createentry          | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:editownentry         | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:annulentry           | Allow      | monlaututoriatutor | System       |           |
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
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    And I log out
    And I am on "local/monlaututoria/entries/create.php?studentid=2&academicyearid=1" logged in as "tutor1"
    And I set the field "Motivo" to "Seguimiento"
    And I set the field "Comentario compartido con el alumno" to "Version original"
    And I press "Registrar tutoría"
    And I log out

  Scenario: The author edits their own recent entry without needing a reason
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I click on "Ver detalle" "link"
    When I click on "Editar tutoría" "link"
    And I set the field "Comentario compartido con el alumno" to "Version corregida"
    And I press "Guardar cambios"
    Then I should see "Tutoría actualizada correctamente."
    And I should see "Version corregida"

  Scenario: A tutor without editownentry/editanyentry cannot reach the edit page
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I click on "Ver detalle" "link"
    And I am on "local/monlaututoria/entries/edit.php?id=1" logged in as "tutor2"
    Then I should see "Sorry, but you do not currently have permission to do that"

  Scenario: Annulling an entry requires confirmation and a reason, and the row survives
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I click on "Ver detalle" "link"
    And I click on "Anular tutoría" "link"
    When I press "Confirmar anulación"
    Then I should see "Required"
    When I set the field "Motivo de la anulación" to "El alumno ha cambiado de centro"
    And I set the field "Confirmo que quiero anular esta tutoría." to "1"
    And I press "Confirmar anulación"
    Then I should see "Tutoría anulada correctamente."

  Scenario: An already-annulled entry can no longer be edited
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I click on "Ver detalle" "link"
    And I click on "Anular tutoría" "link"
    And I set the field "Motivo de la anulación" to "El alumno ha cambiado de centro"
    And I set the field "Confirmo que quiero anular esta tutoría." to "1"
    And I press "Confirmar anulación"
    When I am on "local/monlaututoria/entries/edit.php?id=1"
    Then I should see "Esta tutoría ya está anulada."
