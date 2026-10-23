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
use ClinicLE\Module\ModuleCollection;

class ADD_Export_EncounterLookup {
	private $_pageByEncType=array();
	private $_encTypeByPage=array();
	
	public function __construct(ModuleCollection $modules) {
		foreach($modules->getAllPages() as $modName => $pages) {
			foreach($pages as $pageName => $page) {
				$this->_addLookup($page);
			}
		}
	}
	
	public function _addLookup($page) {
        if (!$page->getSetting('encounter')) return;
		if (!$page->getSetting('form')) return;
		$mod = $page->getModule();
        	if($page->hasComponent('Form') && $page->getComponent('Form')->getType()=="Form_File") return;
		$this->_pageByEncType[$page->getSetting('encounter')]=$page;
	
		if (!isset($this->_encTypeByPage[$mod->getName()])) $this->_encTypeByPage[$mod->getName()]=array();
		$this->_encTypeByPage[$mod->getName()][$page->getName()]=$page->getSetting('encounter');
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

