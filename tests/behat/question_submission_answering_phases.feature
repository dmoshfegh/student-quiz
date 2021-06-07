@mod @mod_studentquiz
Feature: Question submission and answering will follow the availability setting
  In order to allow users to submission questions or answering questions only in a limited period
  As a teacher
  I need availability setting for question submission and question answering

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity    | name             | intro                   | course | idnumber     |
      | studentquiz | StudentQuiz Test | StudentQuiz description | C1     | studentquiz1 |
    And the following "questions" exist:
      | questioncategory             | qtype     | name       |
      | Default for StudentQuiz Test | truefalse | Question 1 |
      | Default for StudentQuiz Test | truefalse | Question 2 |
      | Default for StudentQuiz Test | truefalse | Question 3 |

  @javascript
  Scenario: New availability settings should exist
    When I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "StudentQuiz" to section "1"
    And I expand all fieldsets
    Then I should see "Open for question submission from"
    And I should see "Closed for question submission from"
    And I should see "Open for answering from"
    And I should see "Closed for answering from"

  @javascript
  Scenario: Availability settings validation
    When I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "StudentQuiz" to section "1"
    And I expand all fieldsets
    And I set the field "id_name" to "StudentQuiz Test Availability"

    # Submissions deadline can not be specified before the open for submissions date
    And I set the field "id_opensubmissionfrom_enabled" to "1"
    And I set the field "id_closesubmissionfrom_enabled" to "1"
    And I set the availability field "closesubmissionfrom" to "-1" days from now
    And I press "Save and display"
    Then I should see "Submissions deadline can not be specified before the open for submissions date"

    # Answering deadline can not be specified before the open for answering date
    And I set the field "id_opensubmissionfrom_enabled" to "0"
    And I set the field "id_closesubmissionfrom_enabled" to "0"
    And I set the field "id_openansweringfrom_enabled" to "1"
    And I set the field "id_closeansweringfrom_enabled" to "1"
    And I set the availability field "closeansweringfrom" to "-1" days from now
    And I press "Save and display"
    Then I should see "Answering deadline can not be specified before the open for answering date"

  Scenario: Availability settings for question submission
    When I am on the "StudentQuiz Test" "mod_studentquiz > View" page logged in as "admin"
    Then the "Create new question" "button" should be enabled

    # Enable only for Open for question submission (Future)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_opensubmissionfrom_enabled" to "1"
    And I set the availability field "opensubmissionfrom" to "+5" days from now
    And I press "Save and display"
    And the "Create new question" "button" should be disabled
    And I should see "Open for question submission from"

    # Enable only for Open for question submission (Past)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the availability field "opensubmissionfrom" to "-5" days from now
    And I press "Save and display"
    And the "Create new question" "button" should be enabled
    And I should not see "Open for question submission from"

    # Enable only for Close for question submission (Past)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_opensubmissionfrom_enabled" to "0"
    And I set the field "id_closesubmissionfrom_enabled" to "1"
    And I set the availability field "closesubmissionfrom" to "-5" days from now
    And I press "Save and display"
    And the "Create new question" "button" should be disabled
    And I should see "This StudentQuiz closed for question submission on"

    # Enable only for Close for question submission (Future)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the availability field "closesubmissionfrom" to "+5" days from now
    And I press "Save and display"
    And the "Create new question" "button" should be enabled
    And I should see "This StudentQuiz closes for question submission on"

    # Enable both Open and Close for question submission (Open in the Past)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_opensubmissionfrom_enabled" to "1"
    And I set the field "id_closesubmissionfrom_enabled" to "1"
    And I set the availability field "opensubmissionfrom" to "-5" days from now
    And I set the availability field "closesubmissionfrom" to "+5" days from now
    And I press "Save and display"
    And the "Create new question" "button" should be enabled
    And I should see "This StudentQuiz closes for question submission on"

    # Enable both Open and Close for question submission (Open in the Future)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_opensubmissionfrom_enabled" to "1"
    And I set the field "id_closesubmissionfrom_enabled" to "1"
    And I set the availability field "opensubmissionfrom" to "+5" days from now
    And I set the availability field "closesubmissionfrom" to "+10" days from now
    And I press "Save and display"
    And the "Create new question" "button" should be disabled
    And I should see "Open for question submission from"

  Scenario: Availability settings for question answering
    When I am on the "StudentQuiz Test" "mod_studentquiz > View" page logged in as "admin"
    Then the "Start Quiz" "button" should be enabled

    # Enable only for Open for question answering (Future)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_openansweringfrom_enabled" to "1"
    And I set the availability field "openansweringfrom" to "+5" days from now
    And I press "Save and display"
    And the "Start Quiz" "button" should be disabled
    And I should see "Open for answering from"

    # Enable only for Open for question answering (Past)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the availability field "openansweringfrom" to "-5" days from now
    And I press "Save and display"
    And the "Start Quiz" "button" should be enabled
    And I should not see "Open for answering from"

    # Enable only for Close for question answering (Past)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_openansweringfrom_enabled" to "0"
    And I set the field "id_closeansweringfrom_enabled" to "1"
    And I set the availability field "closeansweringfrom" to "-5" days from now
    And I press "Save and display"
    And the "Start Quiz" "button" should be disabled
    And I should see "This StudentQuiz closed for answering on"

    # Enable only for Close for question answering (Future)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the availability field "closeansweringfrom" to "+5" days from now
    And I press "Save and display"
    And the "Start Quiz" "button" should be enabled
    And I should see "This StudentQuiz closes for answering on"

    # Enable both Open and Close for question answering (Open in the Past)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_openansweringfrom_enabled" to "1"
    And I set the field "id_closeansweringfrom_enabled" to "1"
    And I set the availability field "openansweringfrom" to "-5" days from now
    And I set the availability field "closeansweringfrom" to "+5" days from now
    And I press "Save and display"
    And the "Start Quiz" "button" should be enabled
    And I should see "This StudentQuiz closes for answering on"

    # Enable both Open and Close for question answering (Open in the Future)
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_openansweringfrom_enabled" to "1"
    And I set the field "id_closeansweringfrom_enabled" to "1"
    And I set the availability field "openansweringfrom" to "+5" days from now
    And I set the availability field "closeansweringfrom" to "+10" days from now
    And I press "Save and display"
    And the "Start Quiz" "button" should be disabled
    And I should see "Open for answering from"
