@mod @mod_studentquiz
Feature: Stable restore of moodle course backups
  In order to keep the studentquiz repository healthy
  As a code contributor
  I need to be able to verify my changes are still restorable

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity    | name          | intro              | course | idnumber     | publishnewquestion |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1 | 1                  |
    And the following "questions" exist:
      | questioncategory          | qtype | name                       | questiontext                  |
      | Default for StudentQuiz 1 | essay | Test question to be copied | Write about whatever you want |

  @javascript
  Scenario: Backup and restore of the current course
    When I log in as "admin"
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | Course 2 |
    Then I should see "Course 2"

    And I go to the courses management page
    And I click on "delete" action for "Course 1" in management course listing
    And I press "Delete"
    And I should see "Deleting C1"
    And I should see "C1 has been completely deleted"

    And I am on the "StudentQuiz 1" "mod_studentquiz > View" page
    And I should see "Create new question"
    And I should see "Test question to be copied"
    And "Start Quiz" "button" should exist
