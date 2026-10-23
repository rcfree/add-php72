<?php
class ADD_Component_Encounter_Single_RepeatPrelim extends ADD_Component_Encounter_Single {
    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
        $add = ADD::sys();
		if($idValue) {
		    $record = $add->getEncounterManager()->recordById($idValue);
			$record->retrieve();
		}
		else {
			$record = new \ClinicLE\Data\Record\EncounterRecord();
			$record->setType($page->getSetting('encounter'));
			$record->setPatient($add->getPatientId());
            $record->setNew(true);
		}
        return $record;
    }
	
	public function populate() {
		$add = ADD::sys();
        $page = $this->_page;
		
		$action = $add->getParam('action');
		$eid = $add->getParam('eid');
		if($action=="create") {
            $add->setState('new',true);
			return;
		}
		
		$er = $this->getEncounterRecord($eid);
		if (!$er->exists()) throw new Exception("Record with ID ".$eid." not found");
		
		if($action=="delete") {
			return;
		}
		
        $er->retrieve();
			
        $page->setData($er->getObsValues());
        $add->setState('new',false);
	}	
	
	function submit($values) {
		$add = ADD::sys();
		$page = $this->_page;
		if ($add->hasState('locked')) return;
		$form = $page->getComponent('Form');

		foreach($form->getFields() as $field) {
			if (is_subclass_of($field,"ADD_Field_SubmitInterface")) {
				$field->submitValues($values,$page);
			}
		}
		$action = $add->getParam('action');
		$eid = $add->getParam('eid');
		$title = $page->getSetting('title');
		$encType = $page->getSetting('encounter');
		$pid = $add->getParam('pid');
		
		if ($action=="create") {
			$er = $page->getEncounterRecord();
            $er->setEncData('is_autosave',$add->hasState('autosave') ? 1 : 0);
	        $er->setEncData('is_invalid',$add->hasState('invalid') ? 1 : 0);
            $er->setPatient($pid);
			$er->setNew(true);
			
            $add->setState('new',true);

            $er->store($values);
			$page->addMessage("Created and saved new '$title' record."); 
			
            $this->_afterSubmit($values,$page);
			$menuPath = $page->createMenuPath();
			$menuParams = array("pid"=>$pid,"eid"=>$er->getId());
			$add->setState('redirect',ADD_Util::uri($menuPath,$menuParams));
			return;
		}
		
		$er = $page->getEncounterRecord($eid);
                        
        
        $er->setEncData('is_autosave',$add->hasState('autosave') ? 1 : 0);
        $er->setEncData('is_invalid',$add->hasState('invalid') ? 1 : 0);

		#otherwise update the existing one
		$er->retrieve(true);
		$er->setObsValues($values);
                
		$add->setState('new',false);
               
		if ($er->isChanged()) {
			$er->store();
			$page->addMessage("Updated and saved '$title' record (revision:".$er->getEncData('revision_no').").");
		}
		else {
			$page->addMessage("No changes were made to '$title' record.");
		}
		$this->_afterSubmit($values,$page);
	}
}
?>