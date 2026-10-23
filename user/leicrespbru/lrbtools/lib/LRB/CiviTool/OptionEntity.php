<?php
/*
 * Class for CiviCRM option value/group entities and actions
 */



class LRB_CiviTool_OptionEntity extends LRB_CiviTool_Entity {
	protected $optionGroupName=null;
	
	function get() {
		return $this->getOptionValue($this->getOption(LRB_CiviTool_Entity::NAME), $this->optionGroupName);
	}
	
	function getValue() {
		$vals = $this->get();
		return $vals['value'];
	}
	function count() {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionValue.php";
		
		list($label,$name) = $this->getLabelAndName();
		$params['name']=$name;
		$params['version']=3;
		$params['sequential']=1;
		$params['option_group_id']=$this->getOptionGroupID($this->optionGroupName);
		$ent = civicrm_api3_option_value_get($params);
		return $ent['count'];
	}
	
	
	function delete() {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionValue.php";
		
		$group = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>$this->optionGroupName));
		$groupId = $group->getId();
		
		list($label,$name) = $this->getLabelAndName();
		
		$value = $this->getOptionValue($name, $this->optionGroupName);
		
		if (isset($value['is_reserved']) && $value['is_reserved']==1) throw new LRB_CiviToolException("Unable to delete reserved entity '$name'");
		$valueId = $value['id'];
	
		$params['option_group_id']=$groupId;
		$params['id']=$valueId;
		$params['version']=3;
	
		$ent = civicrm_api3_option_value_delete($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to delete entity");
		}
	}
	function create() {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionValue.php";
	
		if ($this->exists()) {
			throw new LRB_CiviToolException("Error creating OptionEntity ".print_r($this->getOptions(),true)." it already exists");
		}
	
		$group = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>$this->optionGroupName));
		$groupId = $group->getId();
		$params['option_group_id']=$groupId;
		
		$name = $this->getOption(LRB_CiviTool_Entity::NAME);
		if (!$name) $name = $this->labelToName($this->getOption(LRB_CiviTool_Entity::LABEL));
		
		$params['name']=$name;
		$params['label']=$this->getOption(LRB_CiviTool_Entity::LABEL);
		$params['value']=$this->getOption(LRB_CiviTool_Entity::VALUE);
		$params['version']=3;
		$params['is_active']=1;
		
		$ent = civicrm_api3_option_value_create($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unexpected problem creating Entity ".print_r($this->getOptions(),true));
		}
	}
	
}