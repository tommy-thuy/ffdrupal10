There're 2 way to generate (clone) a new entity from this module

Approach 1: Drush, you can use drush command line like some sample bellow:

	- drush cee-ge 'custom_entity_example' 'my_entity' 'custom-entity-example' 'my-entity' 'CustomEntityExample' 'MyEntity' 'custom entity example' 'my entity' 'Custom Entity Example' 'My Entity'

	- drush cee-ge 'custom_entity_example' 'buyict_proposal' 'custom-entity-example' 'buyict-proposal' 'CustomEntityExample' 'BuyICTProposal' 'custom entity example' 'proposal' 'Custom Entity Example' 'Proposal'

	- drush cee-ge 'custom_entity_example' 'buyict_interview' 'custom-entity-example' 'buyict-interview' 'CustomEntityExample' 'BuyICTInterview' 'custom entity example' 'interview' 'Custom Entity Example' 'Interview'

	- drush cee-ge 'custom_entity_example' 'buyict_invitation' 'custom-entity-example' 'buyict-invitation' 'CustomEntityExample' 'BuyICTInvitation' 'custom entity example' 'invitation' 'Custom Entity Example' 'Invitation'

	- drush cee-ge 'custom_entity_example' 'buyict_milestone' 'custom-entity-example' 'buyict-milestone' 'CustomEntityExample' 'BuyICTMilestone' 'custom entity example' 'milestone' 'Custom Entity Example' 'Milestone'

	- drush cee-ge 'custom_entity_example' 'buyict_award_contract' 'custom-entity-example' 'buyict-award-contract' 'CustomEntityExample' 'BuyICTAwardContract' 'custom entity example' 'award of contract' 'Custom Entity Example' 'Award of Contract'

Approach 2: UI, visit website at the url: 
	- admin/content/custom-entity-example-types/clone-form 
	- make sure the folder location is writable
	- input your module in the destination fields
	- Click on Generate


For both approaches, you will have your new module including the entity with essential functions to use for your projects.

	- You can see your manage entity content in /admin/content
	- You can see your manage entity structure in /admin/structure
