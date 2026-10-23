<?php
/*
 * Entity representing CiviCRM Activity Status and actions extends the OptionEntity
 * which deals with retrieving option values from specific option groups
 */

class LRB_CiviTool_Entity_ActivityStatus extends LRB_CiviTool_OptionEntity {
	
	#the option group used by the entity
	protected $optionGroupName="activity_status";
}