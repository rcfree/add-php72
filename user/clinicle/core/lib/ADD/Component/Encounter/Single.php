<?php

class ADD_Component_Encounter_Single extends ADD_Component_Encounter {
	protected function initEncounterRecord($idValue=null) {
		$page = $this->_page;
		$ppage = $page->getParentPage();
		if(!$ppage) throw new ADD_PageException("Parent page not defined for '".$page->getName()."' page. The Encounter_Single component requires it");
		$per = $ppage->getEncounterRecord($idValue);
		$er = ADD::sys()->getEncounterManager()->child($per,$page->getSetting('encounter'),null,$page->getSetting('badge'));

		return $er;
	}
	function beforeSubmit(&$values) {
		$page = $this->_page;
		
		if(!$page->getSetting('ignore_token')) {
			$tokenNotRegistered = ADD::db('token')->register($values);
			if (!$tokenNotRegistered && $page->hasComponent('SaveButton')) {
				$page->addWarning("This page has been saved previously. To ensure data integrity, please click 'Save Data' to confirm.");
				ADD::sys()->setState('cancel',true);
				return;
			}
		}
		$form = $page->getComponent('Form');
		foreach($form->getFields() as $field) {
			if (is_subclass_of($field,"ADD_Field_SubmitInterface")) {
				$field->beforeSubmitValues($values,$page);
			}
		}
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
		$er = $page->getEncounterRecord();
        $title = $page->getSetting('title');

        $er->setEncData('is_autosave',$add->hasState('autosave') ? 1 : 0);
        $er->setEncData('is_invalid',$add->hasState('invalid') ? 1 : 0);
        
		#if no child encounter then create it
		if (!$er->exists()) {
                $add->setState('new',true);

                $er->store($values);
				$page->addMessage("Created and saved new '$title' record."); 
                $this->_afterSubmit($values,$page);
				return;
		}
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
   
	public function _afterSubmit($values,$page) {
		$form = $page->getComponent('Form');

		foreach($form->getFields() as $field) {
			if (is_subclass_of($field,"ADD_Field_SubmitInterface") && method_exists($field,"afterSubmitValues")) {
				$field->afterSubmitValues($values,$page);
			}
		}
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
            $add->setState('new',true);
		}
	}	
        
}
?>