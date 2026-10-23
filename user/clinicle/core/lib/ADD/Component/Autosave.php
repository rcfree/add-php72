<?php
class ADD_Component_Autosave extends ADD_Component {	
	function afterGenerate() {
        $page = $this->_page;
            if (!$page->hasComponent('Encounter')) return;
		$add = ADD::sys();
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>'__autosave'));
                $page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>'__invalid'));
                
                   $er = $page->getEncounterRecord();
                
            if (!$er->exists()) return;
             if ($er->getEncData('is_autosave') && !$er->getEncData('is_invalid')) {
                $page->addMessage("An ADD user session timed out so data was automatically saved. No invalid data was identified.");
            } 
            if ($er->getEncData('is_autosave') && $er->getEncData('is_invalid')) {
                $page->addJavascript("$('#content-form :input').blur();");
                $page->addError("An ADD user session timed out and invalid data was automatically saved. Please correct it.");
            }
            if (!$er->getEncData('is_autosave') && $er->getEncData('is_invalid')) {
                $page->addError("Invalid data is still present in the form. Please correct it.");
            } 
 	}
	function beforeGenerate() {
            $page = $this->_page;
            if (!$page->hasComponent('Encounter')) return;
            $page->addJavascript("ADD.Form.hasData=true");
        }
        function beforeSubmit(&$values) {
            $page = $this->_page;
            $add = ADD::sys();
            if (!$page->hasComponent('Encounter')) return;
            if (ADD_Util::getArrayValue('__autosave',$values)==1) $add->setState('autosave',true);          
            if (ADD_Util::getArrayValue('__invalid',$values)==1) $add->setState('invalid',true);
        }
}