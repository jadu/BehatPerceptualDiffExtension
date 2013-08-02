Feature: Jadu.net
    Scenario: Finding out more about Weejot
        Given I am on "http://jadu.net"
         When I follow "Mobile"
         Then I should see "The Weejot mobile publishing platform"

    Scenario: Looking for a job
        Given I am on "http://jadu.net"
         When I follow "Company"
          And I follow "Jobs and Careers"
         Then I should see "We're hiring"
