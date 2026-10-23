<?php
class ADD_Component_Locking extends ADD_Component {
	const NO_LOCK_SETTING="no-locking";

	function afterPopulate() {
		$page = $this->_page;
			
		if($page->getSetting(self::NO_LOCK_SETTING) || $this->getSetting("disabled")) return;
		$add = ADD::sys();
        $er = $page->getEncounterRecord();
		$er->retrieve();
                if($er->getEncData('is_invalid')) return;
        
		if (!$er->exists()) {
			$add->setState('locked',false);
		}
		else {
                    if($er->getEncData('is_invalid')) return;
			if ($er->getEncData('is_locked')==1) {
				$add->setState('locked',true);
				$add->setState('readOnly',true);
			}
			if ($er->getEncData('is_complete')==1) {
				$add->setState('complete',true);
			}
		}

		$incompleteFields =  $page->getComponent('RequiredFields')->findIncompleteFields($er->getObsValues());
		
		if (count($incompleteFields)==0) {
			 $add->setState('complete',true);
		}
		else {
			$add->setState('complete',false);
		}
		$isUnlocked = $this->checkEncounterIsComplete($page);
	}
	
	function beforeGenerate() {
		$page = $this->_page;
		if($page->getSetting(self::NO_LOCK_SETTING) || $this->getSetting("disabled")) return;
		$add = ADD::sys();
        if($add->hasState(ADD_System::INUSE)) {
            return;
        }
        $er = $page->getEncounterRecord();
		$er->retrieve();
        if($er->getEncData('is_invalid')) return;
        if($er->getEncData('is_verified')) return;
        if ($add->hasState('locked') && $page->getPermission('edit')) {
			$html = $page->html();
			$html->addAfterElement('opt_start',array('tag'=>'button','text'=>"<i class='icon-lock'></i>&nbsp;Unlock record",'attrs'=>array("class"=>"btn",'name'=>'__unlock_record'),'type'=>ADD_Content_Html::TEXT));
		}
	}
	
	function submitAccess($values) {
		$page = $this->_page;
		
		if($page->getSetting(self::NO_LOCK_SETTING)) return;
		if (!isset($values['__unlock_record'])) return;
        
        $add = ADD::sys();
		$er = $page->getEncounterRecord();
        $er->retrieve();
                $er->setEncData('is_locked',0);
                $er->store();
                $er->audit("unlock");
		$add->setState('locked',false);
		$add->setState('cancel',true);
	}
	
	function submit($values) {
		$page = $this->_page;
		
		$noLocking = $page->getSetting(self::NO_LOCK_SETTING);
		$add = ADD::sys();
        
        $incompleteFields = $page->getComponent('RequiredFields')->findIncompleteFields($values);

		$er = $page->getEncounterRecord();
        $er->retrieve();
        if($er->getEncData('is_invalid')) return;
        
        if (count($incompleteFields)>0) {
			$er->setEncData('is_complete',0);
			if (!$noLocking) $er->setEncData('is_locked',0);
            $add->setState('complete',false);
			if (!$noLocking) $add->setState('locked',false);
		}
		else {
			$er->setEncData('is_complete',1);
			if (!$noLocking) $er->setEncData('is_locked',1);
            if (!$noLocking) $add->setState('locked',true);
			$add->setState('complete',true);
			if (!$noLocking) $er->audit("lock");
		}
		$er->store();
		
		if (!$add->hasState('locked')) {
			$page->addMessage("Record could not be locked because there are missing required observations.");
		}
	}
	
	protected function checkEncounterIsComplete() {
		$add = ADD::sys();
		$isUnlocked = false;
        $page = $this->_page;

        $form = $page->getComponent('Form');
		if ($add->hasState(ADD_System::READONLY)) return null;
		if ($add->hasState('new')) return null;
		if ($add->hasState('inUse')) return null;
		if (!$add->config('locking')) return null;
		if ($add->hasState('cancel')) return null;
		if ($add->hasState('complete')) {
			$isWarning=false;
			$message = "All required observations have been entered.";
			if ($add->hasState('locked')) {
				$message.=" The record is locked to prevent changes.";
				$isUnlocked=true;
			}
			else {
				$message.=" The record should be locked to prevent changes as soon as possible.";
				$isWarning=true;
				$isUnlocked=false;
			}
				
			if ($isWarning) {
				$page->addWarning($message);
			}
			else {
				$page->addMessage($message);
			}
		}
		else {
			$titles = array();
			foreach($page->getComponent('RequiredFields')->findIncompleteFields(null,$page) as $incompleteField) {
				$field = $form->getField($incompleteField);
				if ($field) {
					$field->setIncomplete(true);
					if ($page->getSetting('Locking.incomplete-as-list')) $titles[] = $field->getTitle();
				}
			}
			
            if (!$add->hasState('new')) {
    			if ($page->getSetting('Locking.incomplete-as-list')) {
    				$incFields = $page->getComponent('RequiredFields')->findIncompleteFields(null,$page);
    				$warning = "The following required observations are missing:<br/>- ".implode("<br/>- ",$incFields);
    			}
    			else {
    				$incomCount = count($page->getComponent('RequiredFields')->findIncompleteFields(null,$page));
    				$warning = "There ".($incomCount == 1 ? "is" : "are") ." <b>$incomCount</b> required ".($incomCount == 1 ? "observation" : "observations")." missing in the '".$page->getSetting('title')."' record";
    			}
    			if ($add->hasState('locked')) {
    				$warning.=" (but for some reason the record has been locked)";
    				$isUnlocked=true;
    			}
			
			    $page->addWarning($warning);
            }
		}
		return $isUnlocked;
	}	
}
