#features/domain.feature
Feature: recruit patient component
	Description: Recruitment of patient through ADD into CiviCRM
	Role: As an ADD user
	Benefit: To automatically transfer patient from ADD to CiviCRM and setup their consent etc.
	
#Scenario: Add patient to research domain
#	Given a patient is selected called 'JEFF' 'BRIDGES' with id 'X123456' and NHS no '234322' in the database
#	And the page selected is 'research' in the module 'research'
#	And there is no page encounter already present
#	And I have 'create' permission in the selected page
#	When I attempt to add the patient to the selected page
#	Then a single domain should be present
#	Then a 'Patient was added to a Research successfully' message is present
	
#Scenario: Recruitment page displays correctly when patient added to research module
#	Given a patient is selected called 'JEFF' 'BRIDGES' with id 'X123456' and NHS no '234322' in the database
#	And the page selected is 'research' in the module 'research'
#	And there is no page encounter already present
#	And I have 'create' permission in the selected page
#	When I attempt to add the patient to the selected page
#	And the page selected is 'recruit' in the module 'research'
#	Then I view the page
#	Then the menu option is '&nbsp;Recruit into database'
#	Then the content is "The current patient will be added to the CiviCRM database and their consent set up. Are you sure you wish to do this?&nbsp;Recruit patient"
	
Scenario: Patient 	
	Given a patient is selected called 'JEFF' 'BRIDGES' with id 'X123456' and NHS no '234322' in the database
	And the page selected is 'research' in the module 'research'
	And I have 'create' permission in the selected page
	When I attempt to add the patient to the selected page
	And the page selected is 'recruit' in the module 'research'
	Then I submit the page
	Then I view the page
	Then I should get a warning:
	"""
	Patient is currently being added to CiviCRM
	You can check back here in a few minutes to see if the process is complete.
	"""
	Then the content is like "/Data transfer log:.+ Running recruitment transfer into CiviCRM/"
	Then the sub-page 'consent' observation 'civi_status' is 'IN PROGRESS'
	Then the menu option is '&nbsp;Recruitment in progress'
	And I wait until the sub-page 'consent' observation 'civi_status' is 'SUCCESS' for up to '30' seconds
#Scenario: Run recruitment script
#	Given a patient is selected called 'JEFF' 'BRIDGES' with id 'X123457' and NHS no '234323' in the database
#	And there is no page encounter already present
#	Given I have added them to the 'research' domain
#	Then I submit data to the 'consent' page
#	"""
#	add_to_recruit_db=Y
#	add_to_research_db=Y
#	consent_form_date=12/03/2013
#	ilh_identifier=ILH9999999
#	"""
#	And the page selected is 'recruit' in the module 'research'
#	And I run the recruit patient script
#	Then the sub-page 'consent' observation 'civi_status' is 'PENDING'
#	And I wait until the sub-page 'consent' observation 'civi_status' is 'SUCCESS' for up to '30' seconds
