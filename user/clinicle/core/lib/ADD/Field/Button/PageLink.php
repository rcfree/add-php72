<?php

class ADD_Field_Button_PageLink extends ADD_Field_Button {
	private $requiredAccess = null;
	protected function getLinkTitle($page) {
		$add = ADD::sys();
		
		$encType = $page->assertSetting('encounter');
		
		if ($add->hasState('validation')) {
			return $this->getTitle();
		}
		
		$mod = $page->getModule();
		$currPage = $mod->getPage($this->getSetting('section'));
		if(!$currPage) {
		    throw new ADD_PageNotFoundException($this->getSetting('section')." section page not found in page link");
        }
		$cer = $currPage->getEncounterRecord();
        if ($cer->exists()) {
			if ($cer->getEncData('is_complete')) {
				$this->requiredAccess = "view";
				if ($currPage->getPermission('view')) {
					return $this->getSetting('complete_title','Complete');
				}
				else {
					return "No permission to view";
				}
			}
			else {
				$this->requiredAccess = "edit";
				if ($currPage->getPermission('edit')) {
					return $this->getSetting('incomplete_edit_title','Incomplete');
				}
				else {
					if ($currPage->getPermission('view')) {
						$this->requiredAccess = "view";
						return $this->getSetting('incomplete_view_title','View incomplete record');
					}
					return "No permission to edit";
				}	
			}
		}
		else {
			$this->requiredAccess = "create";
			if ($currPage->getPermission('create')) {
				return $this->getSetting('new_title','New');
			}
			else {
				return "No permission to create";
			}
		}
	}	

	protected function getLinkPath($page) {
		$mod = $page->getModule();
        $currPage = $mod->getPage($this->getSetting('section'));
		if (!$currPage) throw new ADD_FieldException("Unable to find section '{$this->getSetting('section')}' for PageLink");
		
		if (!$currPage->getPermission($this->requiredAccess)) return null;
		
		$add = ADD::sys();
		$params = array("pid"=>$add->getPatientId());
		if ($add->getParam('eid')) $params['eid']=$add->getParam('eid');
		return $currPage->createMenuUri();
		
	}
}
?>