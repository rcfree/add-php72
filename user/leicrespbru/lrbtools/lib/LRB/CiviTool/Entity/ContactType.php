<?php
/*
 * Entity representing CiviCRM Activity Status and actions
 */

class LRB_CiviTool_Entity_ContactType extends LRB_CiviTool_Entity {
	function create() {
		require_once "api/v3/utils.php";
		require_once "api/v3/ContactType.php";
		
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		if (empty($parent)) throw new LRB_CiviToolException("A ContactType 'Parent' (name) must be provided");
		$parParams = array(
				LRB_CiviTool_Entity::NAME => $parent,
				LRB_CiviTool_Entity::TYPE => 'ContactType'
		);
		$parCt = LRB_CiviTool_Entity::factory($parParams);
		$parCtId = $parCt->getId();	
		if (!$parCtId) throw new LRB_CiviToolException("The ContactType parent '$parent' does not exist");
		$params['parent_id']=$parCtId;
		
		list($label,$name) = $this->getLabelAndName();
		
		if ($this->exists()) throw new LRB_CiviToolException("Cannot create ContactType '$label' it already exists");
		if (empty($label)) throw new LRB_CiviToolException("A CustomField 'Label' must be provided");
		
		$params['name']=$name;
		$params['label']=$label;
		$params['version']=3;
		
		$ent = civicrm_api3_contact_type_create($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to create entity");
		}
	}

	function count() {
		$ent = $this->getData();
		return $ent['count'];
	}
	
	public function get() {
		$ent = $this->getData();
		if ($ent['count']==0) {
			return null;
		}
		else {
			return $ent['values'][0];
		}
	}
	
	protected function getData() {
		require_once "api/v3/utils.php";
		require_once "api/v3/ContactType.php";
		$parent = $this->getOption(LRB_CiviTool_Entity::PARENT);
		list($label,$name) = $this->getLabelAndName();
		
		$params['name']=$name;
		//$params['custom_group_id']=$cg->getId();
		$params['version']=3;
		$params['sequential']=1;
		$ent = civicrm_api3_contact_type_get($params);
		if (isset($ent['error'])) {
			throw LRB_CiviToolException("There was an error (".$ent['error']." getting the ". get_class($this)." entity with options (".print_r($this->getOptions().")"));
		}
		
		return $ent;
	}
	
	function delete() {
		require_once "api/v3/utils.php";
		require_once "api/v3/ContactType.php";
		
		list($label,$name) = $this->getLabelAndName();
		
		if (!$this->exists()) throw new LRB_CiviToolException("Cannot delete ContactType '".$name."' it does not exist");
		$ctId = $this->getId();
		$params['name']=$name;
		$params['id']=$ctId;
		$params['version']=3;
	
		$ent = civicrm_api3_contact_type_delete($params);
		if (isset($ent['error'])) {
			throw new LRB_CiviToolException("Unable to delete entity");
		}
	}
	
}
