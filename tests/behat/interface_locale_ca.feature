# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria @language
Feature: Catalan interface for local_monlaututoria
  In order to work in Catalan
  As an administrator with Catalan selected
  I should see the plugin strings in Catalan

  Scenario: Academic years page is shown in Catalan
    Given the following config values are set as admin:
      | lang | ca |
    And I log in as "admin"
    When I navigate to "Plugins > Local plugins > Monlau Tutoria > Cursos acadèmics" in site administration
    Then I should see "Cursos acadèmics"
    And I should see "No hi ha cap curs acadèmic actiu"
