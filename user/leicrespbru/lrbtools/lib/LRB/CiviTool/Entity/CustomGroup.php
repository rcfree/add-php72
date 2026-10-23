<?php
/*
 * Entity representing CiviCRM CustomGroup and actions
 * This has to deal with extending specific entities (e.g. contact, individual etc.)
 * but it would be nice to do it in a more coherent fashion than the API does
 */
class LRB_CiviTool_Entity_CustomGroup extends LRB_CiviTool_Entity {
	private $ogLookup = array(
			'Activity' => 'Activity Type',
			'Case' => 'Case Type'
	);
	
	function create() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomGroup.php";
		$parent = $this->getExtendsArray($this->getOption(LRB_CiviTool_Entity::PARENT));
		
		list($title,$name) = $this->getLabelAndName();
		if ($this->exists()) throw new LRB_CiviToolException("Cannot create CustomGroup '$name'/'$title' it already exists");
		
		$style = $this->getOption(LRB_CiviTool_Entity::STYLE,"Inline");
		
		if (!empty($parent)) $params['extends']=$parent;
		$params['name']=$name;
		$params['title']=$title;
		$params['is_active']=1;
		$params['version']=3;
		$params['style']=$style;
		$params['weight']=$this->getOption(LRB_CiviTool_Entity::WEIGHT);
		$params['help_pre']=$this->getOption(LRB_CiviTool_Entity::PRETEXT);
		
		$ent = civicrm_api3_custom_group_create($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to create entity");
		}
	}
	
	protected function getExtendsArray($extends) {
		if (empty($extends)) throw new LRB_CiviToolException("No 'Parent/Extends' option provided for CustomGroup");
		
		//equals sign separates type and sub-types
		//e.g. Activity=Meeting,Print PDF Letter
		$temp = split('=',$extends);
		$extendType = null;
		$subOptions = array();
		if (count($temp)==1) {
			$extendType = $extends;
		}
		else {
			$extendType = $temp[0];
			//sub-types separated by comma
			$subOptions = split(',',$temp[1]);
		}

		//for contact and sub-contact type CustomGroups works with names/labels of sub-types
		if ($extendType=='Contact' || $extendType=='Individual' || $extendType=='Household' || $extendType=='Organization') { 
			return count($subOptions)==0 ? $extendType : array($extendType, $subOptions);
		}
		
		//for none Contacts lookup the 'extends' options using the lookup array above
		//if the option doesn't exist in civi it causes BIG problems
		$ovLookup=null;
		if (isset($this->ogLookup[$extendType])) {
			$ovLookup = $this->ogLookup[$extendType];
		}
		else {
			throw new LRB_CiviToolException("Sorry the '$extendType' is not supported at this time");
		}
		
		if (count($subOptions)==0) return array($extendType);

		//other type custom groups (e.g. Activity, Case etc.) require a lookup of the option group value
		$subIds = array();
		foreach($subOptions as $option) {
			//$ovLabel = $this->getOption($option);
			$ovData = $this->getOptionValue($option, $ovLookup, true);
			if (empty($ovData['value'])) throw new LRB_CiviToolException("Unable to get sub-type '".$option."' for CustomGroup");
			$subIds[]=$ovData['value'];
		}
		$output = array($extendType,$subIds);
		return $output;
	}
	
	function get() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomGroup.php";
		list($label,$name) = $this->getLabelAndName();
		$params['name']=$name;
		$params['title']=$label;
		$params['version']=3;
		$params['sequential']=1;
		$ent = civicrm_api3_custom_group_get($params);
		if ($name=="my_custom_contact_group") print_r($ent);
		if (isset($ent['error'])) {
			throw LRB_CiviToolException("There was an error (".$ent['error']." getting the ". get_class($this)." entity with options (".print_r($this->getOptions().")"));
		}
		
		if ($ent['count']==0) {
			return null;
		}
		else {
			return $ent['values'][0];
		}
	}
	
	function delete() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomGroup.php";
		if (!$this->exists()) throw new LRB_CiviToolException("Cannot delete CustomGroup it does not exist");
		$cgId = $this->getId();
		list($label,$name) = $this->getLabelAndName();
		$params['name']=$name;
		$params['id']=$cgId;
		$params['version']=3;
		try {
			$ent = civicrm_api3_custom_group_delete($params);
			if (isset($ent['error'])) {
				throw new LRB_CiviToolException("Unable to delete entity");
			}
		}
		catch(Exception $ex) {
			throw new LRB_CiviToolException("Unable to delete entity. An unknown error occurred");
		}	
	}
	
	function count() {
		require_once "api/v3/utils.php";
		require_once "api/v3/CustomGroup.php";
		list($label,$name) = $this->getLabelAndName();
		$params['name']=$name;
		$params['version']=3;
		$params['sequential']=1;
		$ent = civicrm_api3_custom_group_get($params);
		return $ent['count'];
	}
}