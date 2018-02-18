Feature: Remove a test page to the manifest
  As a behat tester, I want remove a test page so it doesn't pollute the user space

  Scenario: Remove the test page
    Given I remove the test class "DisplayLogicTestPage"
    Then I go flush the website, dismissing the dialog
    Then I should not have a test class "DisplayLogicTestPage"