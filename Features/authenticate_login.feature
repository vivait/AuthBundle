Feature: Login
  The user must be able to log into the system

  Background:
    Given there are the following users:
      | username  | fullname    | email                  | password | active | group            | tenant |
      | TestUser1 | Test user   | testuser1@vivait.co.uk | password | yes    | [@Administrator] | [@TE1] |
      | TestUser2 | Test user 2 | testuser2@vivait.co.uk | password | yes    | [@User]          | [@TE1] |

  Scenario: Present Login Page
    Given I am on "/login"
    Then I should see "Please sign in"

  Scenario: Try to access protected page
    Given I am on "/" without redirection
    Then the response status code should be 302
    And I should be redirected
    And I should be on "/login"

  Scenario: Try to login with invalid credentials
    Given I am on "/login"
    When I fill in "_username" with "TestUser1"
    And I fill in "_password" with "wrongpassword"
    And I press "Sign in"
    Then I should see "Login Failed"

  Scenario: Try to login with valid credentials
    Given I am on "/login"
    When I fill in "_username" with "TestUser2"
    And I fill in "_password" with "password"
    And I press "Sign in" without redirection
    Then the response status code should be 302
    And I should be redirected
    Then I should see "Test user 2"

  Scenario: Logout user
    Given I am on "/login"
    When I fill in "_username" with "TestUser1"
    And I fill in "_password" with "password"
    And I press "Sign in"
    Then I should see "Test user"
    When I follow "Log Out" without redirection
    Then the response status code should be 302
    And I should be redirected
    And I should see "Please sign in"


