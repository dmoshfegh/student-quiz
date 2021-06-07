@mod @mod_studentquiz
Feature: Backup and restore of studentquizzes
  In order to reuse my studentquizzes
  As a teacher
  I need to be able to back them up and restore them in the question bank

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
      | activity    | name          | intro              | course | idnumber        |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1    |

  @javascript @_file_upload
  Scenario: Restore a Studentquiz 2.0.3 xml backup
    When I am on the "StudentQuiz 1" "mod_studentquiz > View" page logged in as "teacher1"
    And I navigate to "Import" in current page administration
    #And I set the field "format" to "xml" #seems not to work, thus workaround:
    And I click on "#id_format_xml" "css_element"
    And I upload "mod/studentquiz/tests/fixtures/studentquiz-export-v2.0.3.xml" file to "Import" filemanager
    #The "Import" button is not the only clickable item with a text beginning with "Import", so need to specify exactly
    And I click on "#id_submitbutton" "css_element"
    And I wait until the page is ready
    Then I should see "Importing 11 questions from file"
