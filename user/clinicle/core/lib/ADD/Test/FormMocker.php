<?php
$home = getenv('ADD_PATH');
set_include_path(get_include_path().":$home/core/lib:$home/vendor/lib");




/**
 * Check integrity of pages and reports in a module bundle by carrying out common Page generation tasks
 *
 * @author Rob Free
 * @category ADD
 * @package ModuleBundle
 */
class ADD_Test_FormMocker {
    private $_modules = null;
    private $_pageEncs = array();
    private $_pageForms = array();
	
	function __construct(ADD_ModuleCollection $modules) {
		$this->_modules = $modules;
	}
	
	function getPageEnc($encType) {
		return ADD_Util::val($encType,$this->_pageEncs);
	}

    public function addFormMocksToModules() {
		foreach($this->_modules as $mod) {
			foreach($mod->getPages() as $page) {
				if ($page->hasComponent('Form') && get_class($page->getComponent('Form'))=="ADD_Component_Form_DB") $page->getComponents()->add("Form_File");
                if ($page->getSetting('encounter') && $page->getSetting('form')) {
					$this->_pageEncs[$page->getSetting('encounter')]=array($mod->getName(),$page->getName());
				}
				if (!$page->getSetting('encounter') && $page->getSetting('form')) $this->_pageForms[$page->getSetting('form')]=array($mod->getName(),$page->getName());
			}
		}
	}
    public function addFormDependency($page) {
        if($page->getSetting('encounter') && $page->getSetting('form')) {
			list($modName,$pageName) = ADD_Util::val($page->getSetting('encounter'),$this->_pageEncs,array(null,null));
		}
		else if($page->getSetting('encounter')) {
			list($modName,$pageName) = ADD_Util::val($page->getSetting('encounter'),$this->_pageEncs,array(null,null));
		}
		else if($page->getSetting('form')) {
			list($modName,$pageName) = $this->_pageForms[ $page->getSetting('form') ];
		}
		if(!isset($modName)) return;
		$ecomp = $page->getComponents()->get('Form');
		$epage = $this->_modules->getPageInModule($modName,$pageName);
        $ecomp->setPage($epage);
        $ecomp->beforeInstall();
		$ecomp->setup();
		$ecomp->setPage($page);
    }
}
