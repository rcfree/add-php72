<?php
namespace ClinicLE\Module;
use ClinicLE\Module\Exception\ItemUndefinedException;
class ModuleCollection implements \Iterator {
    private $_position=0;
    private $_modules = array();
    private $_aliases=array();
    public function rewind() {
        $this->_position=0;
        $this->_keys = array_keys($this->_modules);
    }
    
    public function current() {
        return $this->_modules[$this->_keys[$this->_position]];
    }
    public function next() {
        $this->_position++;
    }
    public function valid() {
        return isset($this->_keys[$this->_position]);
    }

    public function key() {
        return $this->_keys[$this->_position];
    }
    
    function add(Module $mod) {
        $this->_modules[$mod->getName()]=$mod;
		$this->_aliases[$mod->getAlias()]=$mod->getName();
    }
    function get($name) {
        if(isset($this->_modules[$name])) {
			return $this->_modules[$name];
		}
		if (isset($this->_aliases[$name])) {
			return $this->_modules[$this->_aliases[$name]];
		}
		return null;
    }
    function remove($name) {
		unset($this->_modules[$name]);
	}
    public function getAllPages() {
        $pages = array();
        foreach($this->_modules as $modName => $module) {
            $pages[$modName]=array();
            foreach($module->getPages() as $name => $page) {
                    $pages[$modName][$name]=$page;
            }
        }	
        return $pages;
    }
    
    function getPageInModule($modName,$pageName) {
            $mod = $this->get($modName);
			
            if (!$mod) {
            	$temp = explode('_',$modName);
            	if(count($temp)==1) {
					throw new ItemUndefinedException("Module '$modName' does not exist");
				}
				return $this->getPageInModule($temp[0],$pageName);
			}
            $page = $mod->getPageInStructure($pageName);
            if (!$page) {
				throw new ItemUndefinedException("Page '$pageName' does not exist in module '$modName'");
			}
			return $page;
    }
    
    function asArray() { 
        return $this->_modules;
    }
}
