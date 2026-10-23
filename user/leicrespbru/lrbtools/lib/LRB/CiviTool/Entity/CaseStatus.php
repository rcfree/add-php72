<?php
/*
 * Entity representing CiviCRM Activity Status and actions extends the OptionEntity
 * which deals with retrieving option values from specific option groups
 */

class LRB_CiviTool_Entity_CaseStatus extends LRB_CiviTool_OptionEntity {
	
	#the option group used by the entity
	protected $optionGroupName="case_status";
}