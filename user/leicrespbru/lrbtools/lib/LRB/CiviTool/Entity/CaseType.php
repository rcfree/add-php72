<?php
/*
 * Entity representing CiviCRM Activity Status and actions
 */

class LRB_CiviTool_Entity_CaseType extends LRB_CiviTool_OptionEntity {
	
	#the option group used by the entity
	protected $optionGroupName="case_type";
	function getId() {
		$ct = $this->get();
		if (!$ct) throw new LRB_CiviToolException("Case type '".$this->getOption('Name')."' not found");
		
		return $ct['value'];
	}	
}
