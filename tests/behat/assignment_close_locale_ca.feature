# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria @language
Feature: Catalan interface for closing an assignment
  In order to work in Catalan
  As an administrator with Catalan selected
  I should see the assignment close form in Catalan

  Background:
    Given the following config values are set as admin:
      | lang | ca |
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
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignacions" in site administration
    And I press "Nova assignació"
    And I set the field "Alumne" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "Nova assignació"

  Scenario: The close page is shown in Catalan
    When I click on "Tancar" "link"
    Then I should see "Tancar assignació"
    And I should see "Motiu de tancament"
    And I should see "Confirmo que vull tancar aquesta assignació."
