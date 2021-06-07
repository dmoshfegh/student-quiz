@mod @mod_studentquiz
Feature: New activities instances setting will be inherited from Admin setting
  In order not to change the allowed question types every time it was created
  As a teacher
  I need a default allowed question types option Admin setting and new StudentQuiz will use it

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |

  Scenario: Check Default question types appear in Admin setting
    When I log in as "admin"
    And I navigate to "Plugins > StudentQuiz" in site administration
    Then I should see "Allowed question types"
    And the following fields match these values:
      | s_studentquiz_defaultqtypes[multichoice] | 1 |
      | s_studentquiz_defaultqtypes[truefalse]   | 1 |
      | s_studentquiz_defaultqtypes[shortanswer] | 1 |

  @javascript
  Scenario: Check new instance will get the default question types from Admin setting
    When I log in as "admin"
    And the following config values are set as admin:
      | defaultqtypes | truefalse | studentquiz |
    And I am on "Course 1" course homepage with editing mode on
    And I add a "StudentQuiz" to section "1"
    And I expand all fieldsets
    Then the following fields match these values:
      | allowedqtypes[truefalse]   | 1 |
      | allowedqtypes[ALL]         | 0 |
      | allowedqtypes[multichoice] | 0 |
      | allowedqtypes[shortanswer] | 0 |
    And I press "Cancel"
    And the following config values are set as admin:
      | defaultqtypes | truefalse,multichoice | studentquiz |
    And I add a "StudentQuiz" to section "1"
    And I expand all fieldsets
    And the following fields match these values:
      | allowedqtypes[truefalse]   | 1 |
      | allowedqtypes[multichoice] | 1 |
      | allowedqtypes[ALL]         | 0 |
      | allowedqtypes[shortanswer] | 0 |
