# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria @language
Feature: Catalan interface for assignment creation
  In order to work in Catalan
  As an administrator with Catalan selected
  I should see the assignment creation form in Catalan

  Scenario: Assignment creation page is shown in Catalan
    Given the following config values are set as admin:
      | lang | ca |
    And I log in as "admin"
    When I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration
    Then I should see "Assignacions"
    When I press "Nova assignació"
    Then I should see "Marcar com a tutor principal"
