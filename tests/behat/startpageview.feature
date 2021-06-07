@mod @mod_studentquiz
Feature: View comprehensive information about this studentquiz activity
  In order to see the important information
  As a user
  I need to see all of it when I open the studentquiz activity

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity    | name          | intro              | course | idnumber     | publishnewquestion |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1 | 1                  |
    And the following "questions" exist:
      | questioncategory          | qtype | name                       | questiontext                  |
      | Default for StudentQuiz 1 | essay | Test question to be copied | Write about whatever you want |

  @javascript
  Scenario: Check if the default filter settings are visible
    When I am on the "StudentQuiz 1" "mod_studentquiz > View" page logged in as "student1"
    And I wait until the page is ready
    Then "Filter" "fieldset" should be visible
    And I should see "Fast filter for questions"
