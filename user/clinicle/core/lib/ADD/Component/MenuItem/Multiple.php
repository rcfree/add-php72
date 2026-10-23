<?php

class ADD_Component_MenuItem_Multiple extends ADD_Component_MenuItem_Single {
	public function menuLabel(&$menuLabel) {
            $page = $this->_page;
			$title = $page->getSetting('title');
            $ppage = $page->getParentPage();
            $per = $ppage->getEncounterRecord();
            $encs = $per->exists() ? count(ADD::sys()->getEncounterManager()->children($per,$page->getSetting('encounter'))) : 0;

            $menuLabel = $page->getSetting('title')." [$encs]";
			if(ADD::sys()->config('inplace')) return;
			
            list($create,$edit,$view)=$page->getPermissions(array('create','edit','view'));

            if (!$create && !$edit && $view) $menuLabel.=" (view only)";
	}
}
?>