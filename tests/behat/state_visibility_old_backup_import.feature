@mod @mod_studentquiz
Feature: Restore of studentquizzes in moodle exports contain old approved column
  In order to reuse my studentquizzes
  As a admin
  I need to be able to restore the moodles backups from old studentquizzes, and the state and visibility feature work normally

  Background:
    Given I make sure the current Moodle branch is greater or equal "35"
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And I log in as "admin"

  @javascript @_file_upload
  Scenario: Restore moodle backups containing old StudentQuiz activity with old approved column
    When I am on "Course 1" course homepage
    And I navigate to "Restore" in current page administration
    And I press "Manage backup files"
    And I upload "mod/studentquiz/tests/fixtures/backup-moodle2-aggregated-before.mbz" file to "Files" filemanager
    And I press "Save changes"
    And I restore "backup-moodle2-aggregated-before.mbz" backup into a new course using this options:
    And I am on the "SQbefore" "mod_studentquiz > View" page
    Then I should see "first"
    And I should see "second"
