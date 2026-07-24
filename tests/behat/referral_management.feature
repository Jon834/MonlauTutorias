# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Same disclosed caveats as
# tests/behat/agreement_management.feature (entry id 1 assumed).
@local @local_monlaututoria
Feature: Referrals (phase 6.4)
  In order to route a tutoring case to coordination, orientation or management
  As a tutor
  I need to create a referral with a fresh reason, and coordination needs to be able to assign and resolve it

  Background:
    Given the following "users" exist:
      | username    | firstname   | lastname | email                    |
      | student1    | Student     | One      | student1@example.com    |
      | tutor1      | Tutor       | One      | tutor1@example.com      |
      | coordinator | Coordinator | One      | coordinator@example.com |
      | teacher1    | Teacher     | One      | teacher1@example.com    |
    And the following "roles" exist:
      | name                | shortname                 |
      | Monlau Tutor        | monlaututoriatutor        |
      | Monlau Coordination | monlaututoriacoordination |
    And the following "permission overrides" exist:
      | capability                            | permission | role                      | contextlevel | reference |
      | local/monlaututoria:viewownstudents    | Allow      | monlaututoriatutor        | System       |           |
      | local/monlaututoria:viewstudent        | Allow      | monlaututoriatutor        | System       |           |
      | local/monlaututoria:createentry        | Allow      | monlaututoriatutor        | System       |           |
      | local/monlaututoria:createreferral     | Allow      | monlaututoriatutor        | System       |           |
      | local/monlaututoria:managereferrals    | Allow      | monlaututoriacoordination | System       |           |
    And the following "role assigns" exist:
      | user        | role                      | contextlevel | reference |
      | tutor1      | monlaututoriatutor        | System       |           |
      | coordinator | monlaututoriacoordination | System       |           |
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
    And I am on "local/monlaututoria/student/view.php?id=2" logged in as "tutor1"
    And I click on "Tutorías" "link"
    And I press "Registrar tutoría"
    And I set the field "Comentario compartido con el alumno" to "Seguimiento"
    And I press "Registrar tutoría"
    And I log out

  Scenario: A tutor refers a case and coordination resolves it, without any tutoring relationship
    Given I am on "local/monlaututoria/entries/view.php?id=1" logged in as "tutor1"
    When I click on "Derivar" "link"
    And I set the field "Reason" to "Repeated unexplained absences this term"
    And I press "Derivar"
    Then I should see "Derivación creada correctamente."
    When I am on "local/monlaututoria/referrals/index.php" logged in as "coordinator"
    And I click on "View detail" "link"
    And I press "Resolve"
    And I set the field "Resolution" to "Met with the family, agreed a weekly check-in"
    And I press "Resolve"
    Then I should see "Referral updated successfully."

  Scenario: A user without createreferral cannot reach the creation page
    When I am on "local/monlaututoria/referrals/create.php?entryid=1" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"

  Scenario: A tutor without managereferrals cannot reach the referrals queue
    When I am on "local/monlaututoria/referrals/index.php" logged in as "tutor1"
    Then I should see "Sorry, but you do not currently have permission to do that"
