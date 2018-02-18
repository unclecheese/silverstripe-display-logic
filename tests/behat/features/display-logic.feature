Feature: Add display logic to form fields
  As a cms author
  I want hide and show fields based on the current values of other fields
  So that I only see fields that are relevant to my content.

  Background:
    Given I add a test class "DisplayLogicTestPage"
    And I am logged in with "ADMIN" permissions
    And I go build the database
    Then I should have a test class "DisplayLogicTestPage"
    Given a "UncleCheese\DisplayLogic\DisplayLogicTestPage" "Display Logic Test" has the "Content" "<h1>My awesome headline</h1><p>Some amazing content</p>"
    And I go to "/admin/pages"
    Then I click on "Display Logic Test" in the tree

  Scenario: I can hide an display fields with my input into a form
    When I fill in "VenueSize" with "101"
    Then the element "#Form_EditForm_Refreshments_Holder" should be visible
    When I fill in "VenueSize" with "99"
    Then the element "#Form_EditForm_Refreshments_Holder" should not be visible

    When I fill in "VenueSize" with "200"
    And I check "Refreshments"
    Then the element "#Form_EditForm_Vendors_Holder" should be visible
    When I uncheck "Refreshments"
    Then the element "#Form_EditForm_Vendors_Holder" should not be visible

    When I check "Refreshments"
    And I check "Vendors[test1]"
    Then the element "#Form_EditForm_TentSize_Holder" should not be visible
    When I check "Vendors[test2]"
    Then the element "#Form_EditForm_TentSize_Holder" should not be visible
    And I check "Vendors[test3]"
    Then the element "#Form_EditForm_TentSize_Holder" should be visible

    When I check "Has an upload"
    Then the element "#Form_EditForm_FileUpload_Holder" should be visible
    And the element "#keep-file-small" should be visible
    When I uncheck "Has an upload"
    Then the element "#Form_EditForm_FileUpload_Holder" should not be visible
    And the element "#keep-file-small" should not be visible

    Given the element "#Form_EditForm_LinkLabel_Holder" should not be visible
    When I select the "Link to an internal page" radio button
    Then the element "#Form_EditForm_InternalLinkID_Holder" should be visible
    And the element "#Form_EditForm_LinkLabel_Holder" should be visible
    And the element "#Form_EditForm_ExternalLink_Holder" should not be visible
    And the element "#Form_EditForm_URL_Holder" should not be visible
    And the element "#Form_EditForm_EmbedCode_Holder" should not be visible
    When I select the "Link to an external page" radio button
    Then the element "#Form_EditForm_InternalLinkID_Holder" should not be visible
    And the element "#Form_EditForm_ExternalLink_Holder" should be visible
    And the element "#Form_EditForm_LinkLabel_Holder" should be visible
    And the element "#Form_EditForm_URL_Holder" should be visible
    And the element "#Form_EditForm_UseEmbedCode_Holder" should be visible

    When I check "I have embed code"
    Then the element "#Form_EditForm_EmbedCode_Holder" should be visible
    When I uncheck "I have embed code"
    Then the element "#Form_EditForm_EmbedCode_Holder" should not be visible

    When I fill in "ExternalLink" with "test"
    Then the element "#Form_EditForm_EmbedCode_Holder" should not be visible
    When I fill in "ExternalLink" with "www.youtube.com/123"
    Then the element "#Form_EditForm_EmbedCode_Holder" should be visible
    When I fill in "ExternalLink" with "www.google.com"
    Then the element "#Form_EditForm_EmbedCode_Holder" should not be visible
    When I fill in "ExternalLink" with "vimeo.com/123"
    Then the element "#Form_EditForm_EmbedCode_Holder" should be visible