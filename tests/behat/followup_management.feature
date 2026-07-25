# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Same disclosed caveats as
# tests/behat/agreement_management.feature (entry id 1 assumed).
@local @local_monlaututoria
Feature: Follow-ups (phase 6.2/6.3)
  In order to track a "próximo seguimiento" as a real, closeable action
  As a tutor
  I need to create a follow-up with a due date and priority, and close it either manually or with a new linked entry

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "roles" exist:
      | name         | shortname          |
      | Monlau Tutor | monlaututoriatutor |
    And the following "permission overrides" exist:
      | capability                           | permission | role               | contextlevel | reference |
      | local/monlaututoria:viewownstudents   | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:viewstudent       | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createentry       | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:createfollowup    | Allow      | monlaututoriatutor | System       |           |
      | local/monlaututoria:managefollowups   | Allow      | monlaututoriatutor | System       |           |
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

  Scenario: A tutor creates a follow-up and completes it manually
    Given I am on "local/monlaututoria/entries/view.php?id=1" logged in as "tutor1"
    When I click on "Crear seguimiento" "link"
    And I set the field "Expected date [day]" to "1"
    And I press "Crear seguimiento"
    Then I should see "Seguimiento creado correctamente."
    When I click on "Mark completed" "link"
    And I press "Continue"
    Then I should see "Follow-up updated successfully."

  Scenario: A user without createfollowup cannot reach the creation page
    When I am on "local/monlaututoria/followups/create.php?entryid=1" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"
