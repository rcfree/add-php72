<?php

/*
 * Entity representing CiviCRM Activity Status and actions
 */
class LRB_CiviTool_Entity_OptionValue extends LRB_CiviTool_OptionEntity {
	function get() {
		return $this->getOptionValue($this->getOption(LRB_CiviTool_Entity::NAME), $this->getOption(LRB_CiviTool_Entity::PARENT));
	}
	
	function create() {
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		if (!$parent) throw new LRB_CiviToolException("Cannot create OptionValue '$name' no parent OptionGroup was specified");
		
		$this->optionGroupName=$parent;
		return parent::create();
	}
	
	function delete() {
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		if (!$parent) throw new LRB_CiviToolException("Cannot delete OptionValue '$name' no parent OptionGroup was specified");
		
		$this->optionGroupName=$parent;
		parent::delete();
	}
	
	function exists() {
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		if (!$parent) throw new LRB_CiviToolException("Cannot delete OptionValue '$name' no parent OptionGroup was specified");
		
		$this->optionGroupName=$parent;
		return parent::exists();
	}
}
