<?php
/*
 * Entity representing CiviCRM CustomField and actions
 */
require_once("LRB/CiviTool/Entity.php");

class LRB_CiviTool_Entity_CustomField extends LRB_CiviTool_Entity {
	function create() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomField.php";
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		list($label,$name) = $this->getLabelAndName();
		
		if ($this->exists()) throw new LRB_CiviToolException("Cannot create CustomField '$label' it already exists");
		if (empty($parent)) throw new LRB_CiviToolException("A CustomGroup 'Parent' (name) must be provided");
		if (empty($label)) throw new LRB_CiviToolException("A CustomField 'Label' must be provided");
		
		$cgParams = array(
				LRB_CiviTool_Entity::NAME => $parent,
				LRB_CiviTool_Entity::TYPE => 'CustomGroup'
		);
		
		$cg = LRB_CiviTool_Entity::factory($cgParams);
		$cgId = $cg->getId();
		$params = $this->getOption(LRB_CiviTool_Entity::SETTINGS);
		
		$params['custom_group_id']=$cgId;
		
		if (!$cgId) throw new LRB_CiviToolException("The CustomGroup parent '$parent' was not found");
		$options = $this->getOption(LRB_CiviTool_Entity::OPTIONS);
		if ($options) {
			$ogParams = array(
					LRB_CiviTool_Entity::NAME => $options,
					LRB_CiviTool_Entity::TYPE => 'OptionGroup'
			);
			$og = LRB_CiviTool_Entity::factory($ogParams);
	
			$params['option_group_id']=$og->getId();
		}
		
		
		if (!$name) $name = LRB_CiviTool_Entity::labelToName($label);
		
		$params['name']=$name;
		$params['label']=$this->getOption(LRB_CiviTool_Entity::LABEL);
		$params['help_pre']=$this->getOption(LRB_CiviTool_Entity::PRETEXT);
		$params['weight']=$this->getOption(LRB_CiviTool_Entity::WEIGHT);
		$style = $this->getOption(LRB_CiviTool_Entity::STYLE,"Text:String");
		
		$styleSplit = explode(":",$style);
		$allowedDataTypes = array('String','Int','Float','Money','Memo','Date','Boolean','StateProvince','Country','File','Link','ContactReference');
		$allowedHtmlTypes = array('Text','TextArea','Select','Multi-Select','AdvMulti-Select','Radio','CheckBox','Select Date','Select State/Province','Select Country','Multi-Select Country','Multi-Select State/Province','File','Link','RichTextEditor','Autocomplete-Select');
		
		$htmlType = $style;
		if (isset($styleSplit[1])) {
			$htmlType = $styleSplit[0];	
			$dataType = $styleSplit[1];
			if (!in_array($dataType,$allowedDataTypes)) throw new LRB_CiviToolException("Field '$name' has an invalid data type ($dataType)");
			$params['data_type']=$styleSplit[1];
		}
		if (!in_array($htmlType,$allowedHtmlTypes)) throw new LRB_CiviToolException("Field '$name' has an invalid HTML type ($htmlType)");
		$params['html_type']=$htmlType;
		
		$params['is_active']=1;
		$params['version']=3;
		
		$ent = civicrm_api3_custom_field_create($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to create entity");
		}
	}
	
	private function getData() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomField.php";
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		list($label,$name) = $this->getLabelAndName();
		
		if (empty($parent)) throw new LRB_CiviToolException("A CustomGroup 'name' must be provided");
		$cgParams = array(
				LRB_CiviTool_Entity::NAME => $parent,
				LRB_CiviTool_Entity::TYPE=>'CustomGroup'
		);
		$cg = LRB_CiviTool_Entity::factory($cgParams);
		$params['label']=$label;
		$params['custom_group_id']=$cg->getId();
		$params['version']=3;
		$params['sequential']=1;
		$ent = civicrm_api3_custom_field_get($params);
		
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("There was an error (".$ent['error']." getting the ". get_class($this)." entity with options (".print_r($this->getOptions().")"));
		}
		return $ent;
	}
	
	function get() {
		$ent = $this->getData();
		if ($ent['count']==0) {
			return null;
		}
		elseif ($ent['count']>1) {
			throw new LRB_CiviToolException("There were multiple CustomFields returned with options (".print_r($this->getOptions(),1).")");
		}
		else {
			return $ent['values'][0];
		}
	}
	
	function delete() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomField.php";
		
		list($label,$name) = $this->getLabelAndName();
		
		if (!$this->exists()) throw new LRB_CiviToolException("Cannot delete CustomField '".$name."' it does not exist");
		$cfId = $this->getId();
		$params['name']=$name;
		$params['id']=$cfId;
		$params['version']=3;
	
		$ent = civicrm_api3_custom_field_delete($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to delete entity");
		}
	}	
	
	function count() {
		$ent = $this->getData();
		return $ent['count'];
	}
}