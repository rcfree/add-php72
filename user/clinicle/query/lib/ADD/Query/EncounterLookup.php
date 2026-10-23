<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EncounterLookup
 *
 * @author rcf8
 */
class ADD_Query_EncounterLookup {
	private $_pageByEncType=array();
	private $_encTypeByPage=array();
	
	public function __construct(ADD_ModuleCollection $modules) {
		foreach($modules->getAllPages() as $modName => $pages) {
			foreach($pages as $pageName => $page) {
				$this->_addLookup($page);
			}
		}
	}
	
	public function _addLookup($page) {
		$encType = $page->getSetting('encounter');
		 if (!$encType) return;
		if (!$page->getSetting('form') && $encType) return;
		 if($page->hasComponent('Form') && $page->getComponent('Form')->getType()=="Form_File") return;
		$mod = $page->getModule();
        $this->_pageByEncType[$encType]=$page;
		
		if (!isset($this->_encTypeByPage[$mod->getName()])) $this->_encTypeByPage[$mod->getName()]=array();
		$this->_encTypeByPage[$mod->getName()][$page->getName()]=$encType;
    }
	public function lookupPage($encType) {
		return ADD_Util::val($encType,$this->_pageByEncType);
	}
	public function lookupEncType(ADD_Page $page) {
		$module = $page->getModule();
		$pagesInModule = ADD_Util::val($module->getName(),$this->_encTypeByPage);
		return ADD_Util::val($page->getName(),$pagesInModule);
	}
}

