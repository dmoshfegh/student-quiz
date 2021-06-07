@mod @mod_studentquiz
Feature: Navigation to the pages
  In order no navigate within the studentquiz
  As a teacher
  I need to be able to see the different pages and the question bank

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity    | name          | intro              | course | idnumber     |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1 |
    And the following "questions" exist:
      | questioncategory          | qtype | name          | questiontext                  |
      | Default for StudentQuiz 1 | essay | Test question | Write about whatever you want |
    And I am on the "StudentQuiz 1" "mod_studentquiz > View" page logged in as "teacher1"

  Scenario: See the main page
    When I navigate to "StudentQuiz" in current page administration
    Then I should see "Create new question"
    # Main view, some columns should be fine
    And I should see "Filter"
    And I should see "Difficulty"
    And I should see "Rating"
    And I should see "Comments"
    # Block sidebar
    And I should see "My Progress"
    And I should see "Ranking"

  Scenario: See the statistics page
    When I navigate to "Statistics" in current page administration
    Then I should see "Personal Statistics"
    And I should see "Community Statistics"
    And I should see "Personal Progress"

  Scenario: See the ranking page
    When I navigate to "Ranking" in current page administration
    Then I should see "Published question factor"
    And I should see "Latest correct answer factor"
    And I should see "Total Points"
    And I should see "Personal progress"

  Scenario: See the questionbank
    When I navigate to "Question bank" in current page administration
    Then I should see "Questions"
    And I should see "Categories"
    And I should see "Import"
    And I should see "Export"
    And I should see "Select a category:"

  Scenario: Check that the More link exist in My Progress and Ranking block
    When I navigate to "StudentQuiz" in current page administration
    Then "More" "link" should exist in the "#mod_studentquiz_statblock" "css_element"
    And I click on "More" "link" in the "#mod_studentquiz_statblock" "css_element"
    And I should see "Statistics"
    And I follow "StudentQuiz 1"
    And "More" "link" should exist in the "#mod_studentquiz_rankingblock" "css_element"
    And I click on "More" "link" in the "#mod_studentquiz_rankingblock" "css_element"
    And I should see "Ranking"
