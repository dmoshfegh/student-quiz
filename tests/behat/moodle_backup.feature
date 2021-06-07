@mod @mod_studentquiz
Feature: Backup and restore of moodle exports
  In order to reuse my studentquizzes
  As a admin
  I need to be able to use the moodles backup and restore features

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
    And I log in as "admin"

  @javascript @_file_upload
  Scenario Outline: Restore moodle backups containing old StudentQuiz activity
    When I am on "Course 1" course homepage
    And I navigate to "Restore" in current page administration
    And I press "Manage backup files"
    And I upload "mod/studentquiz/tests/fixtures/<file>" file to "Files" filemanager
    And I press "Save changes"
    And I restore "<file>" backup into a new course using this options:
    And "//*[contains(@href, '#section-999')]" "xpath_element" should not exist
    And I log out
    And the following "course enrolments" exist:
      | user     | course   | role    |
      | student1 | <course> | student |
    And I am on the "<studentquiz>" "mod_studentquiz > View" page logged in as "student1"
    Then I should see "Create new question"
    And "Start Quiz" "button" should exist
    # TODO: These backups have good data selection, we could test for existence and correctness of these
    # TODO: A scenario with the new studentquiz so other datas could be tested too

    Examples:
      | file                                                       | course     | studentquiz   |
      | backup-moodle2-course-one-moodle_31_sq203.mbz              | Course One | StudentQuiz 1 |
      | backup-moodle2-course-two-moodle_35_sq404_missingstate.mbz | Course Two | StudentQuiz 1 |
      | backup-moodle2-course-two-moodle_35_sq404_correctstate.mbz | Course Two | StudentQuiz 1 |
