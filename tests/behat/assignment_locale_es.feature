# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria @language
Feature: Spanish interface for assignment creation
  In order to work in Spanish
  As an administrator with Spanish selected
  I should see the assignment creation form in Spanish

  Scenario: Assignment creation page is shown in Spanish
    Given the following config values are set as admin:
      | lang | es |
    And I log in as "admin"
    When I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    Then I should see "Asignaciones"
    When I press "Nueva asignación"
    Then I should see "Marcar como tutor principal"
