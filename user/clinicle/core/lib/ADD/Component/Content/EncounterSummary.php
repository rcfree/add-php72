<?php

class ADD_Component_Content_EncounterSummary extends ADD_Component {
	
	function getComponentList() {
		$cl = array(
			'PatientCheck',
			'Breadcrumb',
			'Encounter_Single', 
			'Form_DB',
			'MenuItem_Single',
		);
		return $cl;
	}
	function title(&$title) {
        $page = $this->_page;
		$title = $page->getSetting('title');
	}
	public function menuLabel(&$menuLabel) {
        $page = $this->_page;
        if (!$page->getPermission('view')) return;
        
        $menuLabel = $page->getSetting('title');
	}
	function expectedPermissions(&$expPerms) {
		$expPerms+=array('view');
	}
	
	function menuIcon(&$menuIcon) {
        $page = $this->_page;
	    if ($page->getSetting('no-menu')) return;
            $formComp = $page->getComponent('Form');

            $iconIncomplete = false;
            $mod = $page->getModule();
            $page->setupComponents();

            foreach($formComp->getFields() as $field) {
                    if (!is_a($field,"ADD_Field_Button_PageLink")) continue;
                    $cpageName = $field->getSetting('section');
					
                    $cpage = $mod->getPage($cpageName);
					if (!$cpage) throw new ADD_ComponentException("Page '$cpageName' referred to by section in field '{$field->getName()}' does not exist");
                    $cer = $cpage->getEncounterRecord();
                    $name = $cpage->getName();
                    if (!$cer->exists() || !$cer->getEncData('is_complete')) {
                        $iconIncomplete = true;
                        break;
                    }
            }
            $menuIcon = $iconIncomplete ? ADD_Component_RequiredFields_Notify::INCOMPLETE_ICON : ADD_Component_RequiredFields_Notify::COMPLETE_ICON;
	}
	
}
?>