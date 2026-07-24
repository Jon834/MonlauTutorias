# Written against current Moodle Behat step conventions but not executed in this
# environment (no Behat instance available here). Verify step wording against
# the real Moodle 5.1 instance.
@local @local_monlaututoria
Feature: View a student's longitudinal file header (phase 4.1)
  In order to see a student's current tutoring situation at a glance
  As an administrator
  I need to open the student file from the assignments listing

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | tutor1   | Tutor     | One      | tutor1@example.com   |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Academic years" in site administration
    And I press "New academic year"
    And I set the field "Name" to "2026-2027"
    And I set the field "Short name" to "2026-2027"
    And I press "Save changes"
    And I press "Activate"
    And I navigate to "Plugins > Local plugins > Monlau Tutoria > Asignaciones" in site administration

  Scenario: Viewing the student file shows the current primary tutor
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I set the field "Mark as primary tutor" to "1"
    And I press "New assignment"
    When I click on "View file" "link"
    Then I should see "Tutor One"
    And I should see "2026-2027"

  Scenario: A student with no assignments shows the empty state
    When I am on "local/monlaututoria/student/view.php?id=3" logged in as "admin"
    Then I should see "No active primary tutor for this academic year."

  Scenario: A user without capability cannot access the student file
    When I am on "local/monlaututoria/student/view.php?id=3" logged in as "teacher1"
    Then I should see "Sorry, but you do not currently have permission to do that"

  # Phase 4.3: local/monlaututoria:viewownfile is granted to every
  # authenticated user by default (see db/access.php for why), so student1
  # reaches their own file with no capability granted manually — unlike
  # teacher1 above, who is denied because they are NOT viewing their own record.
  Scenario: A student can view their own file without any capability granted manually
    When I am on "local/monlaututoria/student/view.php?id=3" logged in as "student1"
    Then I should not see "Sorry, but you do not currently have permission to do that"

  Scenario: A student's own file view hides the closing/reassignment reason and the link to full detail
    Given I press "New assignment"
    And I set the field "Student" to "Student One"
    And I set the field "Tutor" to "Tutor One"
    And I press "New assignment"
    And I click on "Close" "link"
    And I set the field "Reason for closing" to "Administrative error"
    And I set the field "I confirm I want to close this assignment." to "1"
    And I press "Confirm closure"
    And I log out
    When I am on "local/monlaututoria/student/view.php?id=3&tab=historial" logged in as "student1"
    Then I should see "Tutor One"
    And I should not see "Administrative error"
    And I should not see "View detail"

  # Phase 4.4: a manipulated academicyearid must produce a clear plugin
  # message, not a generic database-exception page.
  Scenario: An invalid academicyearid parameter shows a clear error, not a generic database exception
    When I am on "local/monlaututoria/student/view.php?id=3&academicyearid=999999" logged in as "admin"
    Then I should see "The requested academic year does not exist."
