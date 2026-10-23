<?php

class ADD_Component_Encounter_Single_Modal extends ADD_Component_Encounter_Single {
    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
        return $page->getParentPage()->getEncounterRecord($idValue);
    }
   
    function submitAccess($values) {
        $page = $this->_page;
            $add = ADD::sys();
            $action = ADD_Util::val(ADD_Component::ACTION,$values);
            if (!$action) return;
            $add->setState('action',$action);
            $add->setState('id',ADD_Util::val(ADD_Component::ID,$values,''));
            
            if (!$page->getPermission($add->hasState('action'))) throw new ADD_AccessException("Sorry you do not have permission to $action the ".$page->getSetting('title')." record");
    }
    	
	function submit($values) {
		$add = ADD::sys();
                $page = $this->_page;

		$em = $add->getEncounterManager();
		
		$encType = $page->getSetting('encounter');
		$action = $add->hasState('action');
		if (!$action) return;
		$id = $add->hasState('id');
		
		$ppage = $page->getParentPage();
		$per = $ppage->getEncounterRecord();
		if ($action=="create") {
			$cer = $em->newChild($per,$encType);
            $cer->store($values);
			$page->addMessage(ucfirst($page->getSetting('single'))." record successfully added");
			$add->setState('new',true);
            return;
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
            $page->addMessage(ucfirst($page->getSetting('single'))." record successfully updated");
		}
		if ($action=="delete") {
			$cer->delete();                    
			$page->addMessage(ucfirst($sect->get('single'))." record successfully deleted");
		}
	}

	function beforeGenerate() {
		$this->_page->addJavascript("window.requiredFields={}");
	}
	function afterGenerate() {
            $page = $this->_page;
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>ADD_Component::ACTION));
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>ADD_Component::ID));
	}
        function beforeSubmit(&$values) {
        }
}
?>