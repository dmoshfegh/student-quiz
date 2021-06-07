@mod @mod_studentquiz
Feature: Question states and visibility
  In order not to change the state and visibility of questions
  As a teacher
  I need a question publishing option, a select box allow to change the question state and visibility and filter for states and visibility

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity    | name               | intro                     | course | idnumber     | publishnewquestion |
      | studentquiz | StudentQuiz Test 1 | StudentQuiz description 1 | C1     | studentquiz1 | 0                  |
      | studentquiz | StudentQuiz Test 2 | StudentQuiz description 2 | C1     | studentquiz2 | 1                  |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |

  @javascript
  Scenario: Test Publish new questions setting
    When I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "student1"
    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 01"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"
    And I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page
    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 01"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"
    And I log out
    And I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "student2"
    Then I should not see "TF 01"
    And I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page
    And I should see "TF 01"
    And I log out
    And I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "admin"
    And I should see "TF 01"
    And I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page
    And I should see "TF 01"

  @javascript @_switch_window
  Scenario: Test filter
    When I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page logged in as "student1"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 01"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 02"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 03"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 04"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"

    And I log out
    And I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page logged in as "admin"

    And I click on "Preview" "link" in the "TF 01" "table_row"
    And I switch to "questionpreview" window
    And I set the field "statetype" to "Disapproved"
    And I click on "Change state" "button"
    And I switch to the main window

    And I click on "Preview" "link" in the "TF 02" "table_row"
    And I switch to "questionpreview" window
    And I set the field "statetype" to "Approved"
    And I click on "Change state" "button"
    And I switch to the main window

    And I click on "Preview" "link" in the "TF 03" "table_row"
    And I switch to "questionpreview" window
    And I set the field "statetype" to "Changed"
    And I click on "Change state" "button"
    And I switch to the main window

    And I click on "//a[text() = 'New']" "xpath_element"
    And I press "id_submitbutton"
    Then I should see "TF 04"
    And I should not see "TF 01"
    And I should not see "TF 02"
    And I should not see "TF 03"
    And I click on "Reset" "button"

    And I click on "//a[text() = 'Approved']" "xpath_element"
    And I press "id_submitbutton"
    And I should see "TF 02"
    And I should not see "TF 01"
    And I should not see "TF 03"
    And I should not see "TF 04"
    And I click on "Reset" "button"

    And I click on "//a[text() = 'Disapproved']" "xpath_element"
    And I press "id_submitbutton"
    And I should see "TF 01"
    And I should not see "TF 02"
    And I should not see "TF 03"
    And I should not see "TF 04"
    And I click on "Reset" "button"

    And I click on "//a[text() = 'Changed']" "xpath_element"
    And I press "id_submitbutton"
    And I should see "TF 03"
    And I should not see "TF 01"
    And I should not see "TF 02"
    And I should not see "TF 04"
    And I click on "Reset" "button"

  @javascript
  Scenario: Hide question
    When I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "admin"
    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 01"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"
    And I log out
    And I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "student1"
    Then I should not see "TF 01"
    And I log out
    And I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "admin"
    And I click on "Show" "link" in the "TF 01" "table_row"
    And I log out
    And I am on the "StudentQuiz Test 1" "mod_studentquiz > View" page logged in as "student1"
    And I should see "TF 01"
    And "Hide" "link" should not exist in the "TF 01" "table_row"

  @javascript @_switch_window
  Scenario: Test Studentquiz cannot edit approved/disapproved question
    When I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page logged in as "student1"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 01"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"

    And I click on "Create new question" "button"
    And I set the field "item_qtype_truefalse" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "Question name" to "TF 02"
    And I set the field "Question text" to "The correct answer is false"
    And I press "id_submitbutton"

    Then "Edit" "link" should exist in the "TF 01" "table_row"
    And "Edit" "link" should exist in the "TF 02" "table_row"

    And I log out
    And I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page logged in as "admin"
    And I click on "Preview" "link" in the "TF 01" "table_row"
    And I switch to "questionpreview" window
    And I set the field "statetype" to "Approved"
    And I click on "Change state" "button"
    And I switch to the main window

    And I click on "Preview" "link" in the "TF 02" "table_row"
    And I switch to "questionpreview" window
    And I set the field "statetype" to "Disapproved"
    And I click on "Change state" "button"
    And I switch to the main window

    And I log out
    And I am on the "StudentQuiz Test 2" "mod_studentquiz > View" page logged in as "student1"

    And "Edit" "link" should not exist in the "TF 01" "table_row"
    And "Edit" "link" should not exist in the "TF 02" "table_row"
