<?php

class ADD_Component_MenuItem_Single extends ADD_Component_MenuItem {
	public function menuLabel(&$menuLabel) {
            $page = $this->_page;
			if(!$page->getSetting('encounter')) {
				if ($page->getPermission('view')) {
					$menuLabel=$page->getSetting('title');
					return;
				}
				return;
			}

			try {
				$er = $page->getEncounterRecord(ADD::sys()->getParam('eid'));
                
				list($create,$edit,$view)=$page->getPermissions(array('create','edit','view'));
				
                if ($er && (!$er->exists() && !$create)) return;
                $menuLabel = $page->getSetting('title');
                if ($page->getExpectedPermissions()!=array('view')) {
                    if (!$create && !$edit && $view && $this->getSetting('no_viewonly')) $menuLabel.=" (view only)";
                }
            }
            catch(ADD_EncounterNotFoundException $ex) {
                return;
            }
	}
	function menuPath(&$menuPath) {
        $page = $this->_page;
		$subPath = $page->getName();
		$parentPage = $page->getParentPage();
        $topPages = $page->getModule()->getSuperModule()->getTopPages();
        if (count($topPages)==0) throw new ADD_ComponentException("No 'top' pages defined (e.g. without parent) defined in module");
		$topPath = $topPages[0]->getName();
		
		if (!$parentPage) {
			$menuPath = ADD_Util::makePath(array($topPath,$subPath));
			return;
		}
		$parentPath = $parentPage->getName();
		
		if (!$parentPage) return ADD_Util::makePath(array($topPath,$subPath));
		$menuPath = ADD_Util::makePath(array($topPath,$parentPath,$subPath));
	}
	
	function menuParams(&$menuParams) {
		$add = ADD::sys();
                $page = $this->_page;
		$menuParams['pid']=$add->getPatientId();
		$ppage = $page->getParentPage();
		if(!$ppage) return;
		if(!$ppage->hasComponent('Encounter')) return;
		$penc = $ppage->getComponent('Encounter');
        $per = $ppage->getEncounterRecord(ADD::sys()->getParam('eid'));
			
		//if (is_a($penc,"ADD_Component_Encounter_RepeatSection")) {
			if ($per->exists()) {
				$menuParams['eid']=$per->getId();
			}
		//}
	}
}
?>
