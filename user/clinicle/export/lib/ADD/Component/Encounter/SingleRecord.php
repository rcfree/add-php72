<?php

class ADD_Component_Encounter_SingleRecord extends ADD_Component_Encounter {
    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
		$em = ADD::sys()->getEncounterManager();
        $record = ADD::sys()->getEncounterManager()->recordById(ADD::sys()->getParam('eid'));
		return $record;
    }
	  
	public function populate() {
		$add = ADD::sys();
        $page = $this->_page;
		$er = $page->getEncounterRecord($add->getParam('eid'));
                
		if ($er->exists()) {
			$er->retrieve();
            $page->setData($er->getObsValues());
            $add->setState('new',false);
		}
		else {
            throw new Exception("Encounter record ".$add->getParam('eid')." does not exist");
		}
	}	
	function submit($values) {
		$add = ADD::sys();
		$form = $this->_page->getComponent('Form');
		list($cleanValues,$uncleanValues, $notDefined) = $form->cleanValues($values);
        $page = $this->_page;
        $er = $page->getEncounterRecord();
        $er->retrieve(true);
		$er->setObsValues($cleanValues);
                
        $add->setState('new',false);
        $title = "encounter ".$er->getType();
        if ($er->isChanged()) {
        	$er->store();
            $page->addMessage("Updated and saved '$title' record (revision:".$er->getEncData('revision_no').").");
        }
        else {
            $page->addMessage("No changes were made to '$title' record.");
        }
                
	}
       
}
?>