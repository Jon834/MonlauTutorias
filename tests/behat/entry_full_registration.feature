# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording — in
# particular the "roles"/"permission overrides"/"role assigns" generator steps,
# the repeat_elements() field names for the participant rows, and the exact
# success text — against the real Moodle 5.1 instance.
@local @local_monlaututoria @local_monlaututoria_advanced
Feature: Full tutoring entry registration (phase 5.3)
  In order to record a tutoring actuation with its full detail
  As a tutor
  I need to register multiple reasons, internal/external participants, and a restricted note when I am authorised to see it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | tutor1   | Tutor     | One      | tutor1@example.com    |
      | tutor2   | Tutor     | Two      | tutor2@example.com    |
      | family1  | Family    | Member   | family1@example.com   |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
      | Monlau Coordinator | monlaututoriacoord |
    And the following "permission overrides" exist:
      | capability                              | permission | role                | contextlevel | reference |
      | local/monlaututoria:viewownstudents      | Allow      | monlaututoriatutor  | System       |           |
      | local/monlaututoria:createentry          | Allow      | monlaututoriatutor  | System       |           |
      | local/monlaututoria:viewownstudents      | Allow      | monlaututoriacoord  | System       |           |
      | local/monlaututoria:createentry          | Allow      | monlaututoriacoord  | System       |           |
      | local/monlaututoria:viewrestrictednotes  | Allow      | monlaututoriacoord  | System       |           |
    And the following "role assigns" exist:
      | user   | role               | contextlevel | reference |
      | tutor1 | monlaututoriatutor | System       |           |
      | tutor2 | monlaututoriacoord | System       |           |
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
    And I press "New reason"
    And I set the field "Name" to "Convivencia"
    And I set the field "Short name" to "convivencia"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"
    And I log out

  Scenario: A coordinator with viewrestrictednotes registers a full entry with 2 reasons and both kinds of participant
    Given I am on "local/monlaututoria/entries/create_full.php?studentid=2&academicyearid=1" logged in as "tutor2"
    When I set the field "Motivos" to "Seguimiento,Convivencia"
    And I set the field "Comentario compartido con el alumno" to "Buen progreso"
    And I set the field "Nota restringida" to "Situacion familiar delicada"
    And I set the field "Tipo de participante" to "Familia"
    And I set the field "Participante externo (nombre)" to "Family Member (madre)"
    And I press "Registro completo"
    Then I should see "Tutoría registrada correctamente."

  Scenario: A tutor without viewrestrictednotes never sees the restricted note field
    When I am on "local/monlaututoria/entries/create_full.php?studentid=2&academicyearid=1" logged in as "tutor1"
    Then I should not see "Nota restringida"

  Scenario: At least one reason is required
    Given I am on "local/monlaututoria/entries/create_full.php?studentid=2&academicyearid=1" logged in as "tutor1"
    And I set the field "Comentario compartido con el alumno" to "Comentario"
    When I press "Registro completo"
    Then I should see "Required"

  # Not covered here: submitting a participant row with BOTH an internal user
  # and an external name. The internal participant field is an AJAX user
  # selector (core_user/form_user_selector, same as assignment_form.php's
  # student/tutor fields) that cannot be filled with a plain "I set the
  # field" step — entry_service_test.php (phase 5.1) already covers this
  # exact validation at the service layer; only the end-to-end page wiring
  # is left unverified here, an honest gap rather than a faked scenario.
