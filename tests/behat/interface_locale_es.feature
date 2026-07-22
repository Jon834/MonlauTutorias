# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria @language
Feature: Spanish interface for local_monlaututoria
  In order to work in Spanish
  As an administrator with Spanish selected
  I should see the plugin strings in Spanish

  Scenario: Academic years page is shown in Spanish
    Given the following config values are set as admin:
      | lang | es |
    And I log in as "admin"
    When I navigate to "Plugins > Local plugins > Monlau Tutoria > Cursos académicos" in site administration
    Then I should see "Cursos académicos"
    And I should see "No hay ningún curso académico activo"
