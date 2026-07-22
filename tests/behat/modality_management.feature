# Written against current Moodle Behat step conventions but not executed in this
# environment (no Moodle/Behat instance available). Verify step wording against
# a real run once the local Moodle 5.1 environment exists.
@local @local_monlaututoria
Feature: Manage contact modalities
  In order to record how a tutoring contact took place
  As an administrator
  I need to create, edit and activate modalities

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Contact modalities" in site administration

  Scenario: Administrator sees the seeded modalities
    Then I should see "In person"
    And I should see "Phone"

  Scenario: Administrator creates a new modality
    When I press "New modality"
    And I set the field "Name" to "Custom modality"
    And I set the field "Short name" to "custommodality"
    And I press "Save changes"
    Then I should see "Custom modality"
