# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance — in particular the filemanager upload step
# ("I upload ... file to ... filemanager"), already flagged as the least
# verified part of a Behat feature elsewhere in this project
# (csv_import_preview.feature, phase 3D.2), and the exact response Moodle
# gives for a denied pluginfile.php request (no "I should see" step is a
# perfect fit for a raw file/binary response — this scenario is the least
# verified of this feature, flagged deliberately rather than assumed to work).
@local @local_monlaututoria
Feature: Tutoring entry attachments (phase 5.6)
  In order to keep supporting documents with the tutoring record they belong to
  As a tutor
  I need to upload categorised attachments and be sure nobody outside the entry's scope — including the student — can download them

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

  Scenario: The author uploads a categorised attachment and sees it listed
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I click on "Ver detalle" "link"
    When I click on "Adjuntos de la tutoría" "link"
    And I set the field "Categoría documental" to "Autorización"
    And I upload "local/monlaututoria/tests/fixtures/sample_import.csv" file to "Archivos" filemanager
    And I press "Subir archivos"
    Then I should see "Se han subido 1 archivo(s) correctamente."
    And I should see "sample_import.csv"
    And I should see "Autorización"

  Scenario: A student can never reach their own entry's attachments page
    When I am on "local/monlaututoria/entries/attachments.php?id=1" logged in as "student1"
    Then I should see "You do not have access to this student's tutoring data."

  Scenario: A tutor without any relationship to the student cannot reach the attachments page (IDOR)
    When I am on "local/monlaututoria/entries/attachments.php?id=1" logged in as "tutor2"
    Then I should see "You do not have access to this student's tutoring data."

  # The core security property of this whole increment: pluginfile.php is a
  # direct URL, never routed through entries/attachments.php's checks at
  # all — CLAUDE.md explicitly lists "acceso directo a archivos" as a case
  # that must be tested. This is also the least verified scenario in this
  # feature: the exact wording Moodle shows when a pluginfile callback
  # returns false (send_file_not_found()) needs confirming against the real
  # instance, and constructing the file's exact pluginfile URL by hand here
  # assumes a specific path shape ({itemid}/{filename}) that should also be
  # checked against what moodle_url::make_pluginfile_url() actually produced
  # in the previous scenario.
  Scenario: Direct pluginfile.php access to an attachment by an unrelated tutor is denied
    Given I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I click on "Ver detalle" "link"
    And I click on "Adjuntos de la tutoría" "link"
    And I set the field "Categoría documental" to "Autorización"
    And I upload "local/monlaututoria/tests/fixtures/sample_import.csv" file to "Archivos" filemanager
    And I press "Subir archivos"
    And I log out
    When I am on "pluginfile.php/1/local_monlaututoria/entryattachment/1/sample_import.csv" logged in as "tutor2"
    Then I should see "The requested file could not be found"
