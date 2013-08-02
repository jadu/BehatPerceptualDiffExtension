Feature: Twitter Bootstrap button toggle
    Background:
        Given I am on "http://jadu.github.io/BehatPerceptualDiffExtension/toggle/index.html"

    Scenario: Clicking the button
         When I follow "Click me to toggle!"
          And I follow "Click me to toggle!"
          And I follow "Click me to toggle!"
