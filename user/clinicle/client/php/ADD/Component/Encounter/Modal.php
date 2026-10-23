<?php


class ADD_Component_Encounter_Modal extends ADD_Component_Encounter {
    function initEncounterRecord($idValue = null) {
		$add = ADD::sys();
		$am = $add->getAccessManager();
		$erId = $add->hasState('id');
		
		if($erId) {
			$add->setState('new',false);
		}
		else {
			return null;
		}
		$er = $add->getEncounterManager()->record($this->_page->getSetting('encounter'),$add->getPatientId());
		$er->setBadge($this->_page->getSetting('badge'));
        $er->setId($erId);
		return $er;
	}
	function beforePopulate() {
		$this->_page->setSetting(ADD_Component_Locking::NO_LOCK_SETTING,true);
		if(ADD::sys()->getParam(ADD_Component_Modal::ACTION_GET)!="create") return;
		ADD::sys()->setState('new',true);
	}
	function displayAccess() {
		$add = ADD::sys();
		$action = $add->assertParam(ADD_Component_Modal::ACTION_GET);
        
		$add->setState('action',$action);
        $add->setState('id',$add->getParam(ADD_Component_Modal::ID_GET));
	}
    function submitAccess($values) {
        $page = $this->_page;
		$add = ADD::sys();
		$action = ADD_Util::val(ADD_Component_Modal::ACTION_POST,$values);
		if (!$action) return;
		$add->setState('action',$action);
		$add->setState('id',ADD_Util::val(ADD_Component_Modal::ID_POST,$values,''));
		$add->setState('parent',ADD_Util::val(ADD_Component_Modal::PARENT_POST,$values));

		if (!$page->getPermission($add->hasState('action'))) throw new ADD_AccessException("Sorry you do not have permission to '$action' the ".$page->getSetting('title')." record");
    }
    function populate() {
		$add = ADD::sys();
		  
		if ($add->hasState('action') =='create') {
			$add->setState('new',true);
			return;
		}
		if($add->hasState('action')=='view') {
			$add->setState(ADD_System::READONLY,true);
		}
		$id = $add->hasState('id');
		
		
		$er = $add->getEncounterManager()->recordById($id);
		//($this->_page->getSetting('encounter'),$add->getPatientId());
		//$er->setBadge($page->getSetting('badge'));
		
		if (!$er->exists()) {
			throw new ADD_ComponentException("Encounter record with ID '$id' does not exist");
		}
		$er->retrieve();
		$this->_page->setData($er->getObsValues());
        $add->setState('new',false);
	}
	
	function submit($values) {
		$add = ADD::sys();
        $page = $this->_page;

		$em = $add->getEncounterManager();
		
		$encType = $page->getSetting('encounter');
		$action = $add->hasState('action');
		if (!$action) throw new Exception("No action defined");
		$id = $add->hasState('id');
		
		
		$per = $this->getParentRecord($page,$action);
		
		if(!$per->exists()) {
			throw new Exception("Parent record does not exist:".print_r($per,1));
		}
		if ($action=="create") {
			$cer = $em->newChild($per,$encType,$page->getSetting('badge'));
			$cer->store($values);
			
			$page->addMessage(ucfirst($page->getSetting('title'))." successfully added");
			$add->setState('new',true);
			$add->setState('id',$cer->getId());
			
            return array("id"=>$cer->getId(),"type"=>$cer->getType());
		}
		if ($action=='edit' || $action=='delete') {
			$cer = $em->child($per,$encType,$id);
			
			$cer->retrieve();
		}
		if ($action=='view') {
			throw new ADD_FormException("Cannot submit record ".$page->getSetting('title')." record with ID $id using view action");
		}
		if (!$cer->exists()) throw new ADD_FormException("Unable to find ".$page->getSetting('title')." record with ID '$id'");
		
		if ($action=="edit") {
			$cer->store($values);
            $page->addMessage(ucfirst($page->getSetting('title'))." successfully updated");
		}
		if ($action=="delete") {
			$cer->delete();                    
			$page->addMessage(ucfirst($sect->get('title'))." successfully deleted");
			return;
		}
		return array("id"=>$cer->getId(),"type"=>$cer->getType());
	}
	
	function getParentRecord($page,$action) {
		$add = ADD::sys();
		$em = $add->getEncounterManager();
		$ppage = $page->getParentPage();
		if(!$ppage) throw new Exception("Parent page not found for page '".$page->getName());
		
		$penc = $ppage->getComponent('Encounter');
		if($add->hasState('parent')) {
			return $ppage->getEncounterRecord($add->hasState('parent'));
		}
		if(!is_a($penc,"ADD_Component_Encounter_RepeatSection") && $action!="create") {
			throw new Exception("Cannot ".$action." record because there is no parent provided");
		}
		$patientId = $add->getPatientId();
		//$gppage = $ppage->getParentPage();
		//$gper = $gppage->getEncounterRecord();
		//$gper->retrieve();
		$per = $em->newSection($patientId,$ppage->getSetting('encounter'));
		$per->store();
		return $per;
	}
	function afterGenerate() {
		$page = $this->_page;
		$page->html()->addElement('input',array('type'=>'hidden','value'=>ADD::sys()->getParam(ADD_Component_Modal::ACTION_GET),'name'=>ADD_Component_Modal::ACTION_POST));
		$page->html()->addElement('input',array('type'=>'hidden','value'=>ADD::sys()->getParam(ADD_Component_Modal::ID_GET),'name'=>ADD_Component_Modal::ID_POST));
	}
    
	function expectedPermissions(&$expPerms) {
		$expPerms=array_merge($expPerms,array("create","view","edit","delete"));
	}
	
	function beforeSubmit(&$values) {
		$page = $this->_page;
		$tokenNotRegistered = ADD::db('token')->register($values);
		if (!$tokenNotRegistered && $page->hasComponent('SaveButton')) {
			$page->addWarning("This page has been saved previously. To ensure data integrity, please click 'Save Data' to confirm.");
			ADD::sys()->setState('cancel',true);
			return;
		}
	}
}
?>
