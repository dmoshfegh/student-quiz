@mod @mod_studentquiz
Feature: Quizzes can be startet
  In order to use this plugin
  As a student
  I need the quiz run of an activity to work

  Scenario: An already logged in user can participate a studentquiz meanwhile created
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity    | name          | intro              | course | idnumber     | publishnewquestion |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1 | 1                  |
    And the following "questions" exist:
      | questioncategory          | qtype | name                       | questiontext                  |
      | Default for StudentQuiz 1 | essay | Test question to be copied | Write about whatever you want |
    When I am on the "StudentQuiz 1" "mod_studentquiz > View" page logged in as "student1"
    Then I should see "Create new question"
    And "Start Quiz" "button" should exist

  @javascript
  Scenario: A student can start a quiz on a fresh new activity and can follow the rating rules
    # 'Then I should see ... in the ".qno" "css_element"' doesn't work on Moodle <= 35
    Given I make sure the current Moodle branch is greater or equal "36"
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity    | name          | intro              | course | idnumber     | forcerating | publishnewquestion |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1 | 1           | 1                  |
    When I am on the "StudentQuiz 1" "mod_studentquiz > View" page logged in as "student1"
    Then I should see "Create new question"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I should see "Adding a True/False question"
    And I set the field "Question name" to "Exampe question 1"
    And I set the field "Question text" to "The correct answer is true"
    And I press "id_submitbutton"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I should see "Adding a True/False question"
    And I set the field "Question name" to "Exampe question 2"
    And I set the field "Question text" to "The correct answer is true"
    And I press "id_submitbutton"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I should see "Adding a True/False question"
    And I set the field "Question name" to "Exampe question 3"
    And I set the field "Question text" to "The correct answer is true"
    And I press "id_submitbutton"

    # To sort the question table by question name
    #And I change window size to "large"
    #And I click on ".questionname a" "css_element"
    #And I wait "5" seconds
    #And I change window size to "medium"
    And I click on "Start Quiz" "button"
    # QUESTION 1 mainly
    #Then I should see "Can next?"
    And I should see "1" in the ".qno" "css_element"
    And I should not see "Please rate"
    # Can't navigate before answering question
    And "Previous" "button" should not exist
    And "Next" "button" should not exist
    And "Abort" "button" should exist
    And "Finish" "button" should not exist
    # Must rate before go next
    And I set the field "True" to "1"
    And I press "Check"
    And "Next" "button" should exist
    And I click on "Next" "button"
    And I should see "Please rate"
    # Must also rate when now trying to early abort
    And I click on "Abort" "button"
    And I should see "Please rate"
    And I click on ".rateable[data-rate='1']" "css_element"
    And I click on "Next" "button"
    # QUESTION 2 mainly
    And I should see "2" in the ".qno" "css_element"
    And I should not see "Please rate"
    # Can navigate back when not answered yet
    And "Previous" "button" should exist
    And "Next" "button" should not exist
    And "Abort" "button" should exist
    And "Finish" "button" should not exist
    And I click on "Previous" "button"
    And I should see "1" in the ".qno" "css_element"
    # Can navigate forth because already answered this question
    And I click on "Next" "button"
    And I should see "2" in the ".qno" "css_element"
    # After answering can only navigate back after rating
    And I set the field "False" to "1"
    And I press "Check"
    And I click on "Previous" "button"
    And I should see "Please rate"
    And I click on ".rateable[data-rate='2']" "css_element"
    And I click on "Previous" "button"
    #Then I should see "Can next?"
    And I should see "1" in the ".qno" "css_element"
    And I click on "Next" "button"
    And I click on "Next" "button"
    # QUESTION 3 mainly
    And I should see "3" in the ".qno" "css_element"
    And I should not see "Please rate"
    # When answered can only finish when rated
    And "Previous" "button" should exist
    And "Next" "button" should not exist
    And "Abort" "button" should exist
    And "Finish" "button" should not exist
    # After answering can only finish after rating
    And I set the field "True" to "1"
    And I press "Check"
    And "Previous" "button" should exist
    And "Next" "button" should not exist
    And "Abort" "button" should not exist
    And "Finish" "button" should exist
    And I click on "Finish" "button"
    And I should see "Please rate"
    And I click on ".rateable[data-rate='3']" "css_element"
    And I click on "Finish" "button"
    # Back to main view and check the result numbers
    And "Create new question" "button" should exist
    And I should see "1" in the ".stat.last-attempt-correct" "css_element"
    And I should see "2" in the ".stat.last-attempt-incorrect" "css_element"
    And I should see "0" in the ".stat.never-answered" "css_element"
