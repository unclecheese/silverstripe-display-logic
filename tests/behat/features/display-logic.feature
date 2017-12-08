Feature: Add display logic to form fields
  As a cms author
  I want hide and show fields based on the current values of other fields
  So that I only see fields that are relevant to my content.

  Background:
    Given a "UncleCheese\DisplayLogic\Tests\Behaviour\DisplayLogicTestPage" "Display Logic Test"
    And I am logged in with "ADMIN" permissions
    And I go to "/admin/pages"
    Then I click on "Display Logic Test" in the tree

  Scenario: I can control alignment of selected content
    Given I select "My awesome headline" in the "Content" HTML field
    When I press the "Align right" HTML field button
    Then "My awesome headline" in the "Content" HTML field should be right aligned