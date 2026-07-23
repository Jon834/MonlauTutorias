# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria @language
Feature: Spanish interface for closing an assignment
  In order to work in Spanish
  As an administrator with Spanish selected
  I should see the assignment close form in Spanish

  Background:
    Given the following config values are set as admin:
      | lang | es |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    And I press "Nueva asignación"
    And I set the field "Alumno" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "Nueva asignación"

  Scenario: The close page is shown in Spanish
    When I click on "Cerrar" "link"
    Then I should see "Cerrar asignación"
    And I should see "Motivo de cierre"
    And I should see "Confirmo que deseo cerrar esta asignación."
