<?php
/*
 * Entity representing CiviCRM Activity Status and actions
 */
class LRB_CiviTool_Entity_OptionGroup extends LRB_CiviTool_Entity {
	
	function create() {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionGroup.php";
		list($label,$name) = $this->getLabelAndName();
		if ($this->exists()) throw new LRB_CiviToolException("Cannot create OptionGroup '$name' it already exists");
		
		$params['name']=$name;
		$params['title']=$label;
		$params['is_active']=1;
		$params['version']=3;
		
		$ent = civicrm_api3_option_group_create($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to create OptionGroup '$name'");
		}
	}
	
	function count() {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionGroup.php";
		list($label,$name) = $this->getLabelAndName();
		//$ogName = $this->getOption($name);
		$params['name']=$name;
		$params['version']=3;
		$ent = civicrm_api3_option_group_get($params);
		return $ent['count'];
	}
	
	function get() {
		require_once "api/v3/utils.php";
		require_once "api/v3/OptionGroup.php";
		list($label,$name) = $this->getLabelAndName();
		if (!empty($name)) $params['name']=$name;
		if (empty($name) && !empty($label)) $params['title']=$label;
		
		$params['version']=3;
		$params['sequential']=1;
		
		$ent = civicrm_api3_option_group_get($params);
		
		if (isset($ent['error'])) {
			throw LRB_CiviToolException("There was an error (".$ent['error']." getting the ". get_class($this)." entity with options (".print_r($this->getOptions().")"));
		}
		if (count($ent['values'])==0) return null;
		return $ent['values'][0];
	}
	
	function delete() {
		require_once "api/v3/utils.php";
		require_once "CRM/Core/BAO/OptionGroup.php";
		
		list($label,$name) = $this->getLabelAndName();
		
		if (!$this->exists()) throw new LRB_CiviToolException("Cannot delete OptionGroup '$name' it does not exist");
		$id = $this->getId();
		
		
		CRM_Core_BAO_OptionGroup::del($id);
		
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to delete entity");
		}
	}
	
}