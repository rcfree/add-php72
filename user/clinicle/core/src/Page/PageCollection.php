<?php
namespace ClinicLE\Page;

use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\Page\Exception\PageExistsException;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Module\Module;
use ClinicLE\Common\Config\AbstractConfig;
use CLE;
use ClinicLE\System\System;

class PageCollection implements \Iterator {
    private $position=0;
    private $pages = array();
    protected $_module = null;
    private $keys = array();
    
    public function __construct(Module $module) {
        $this->_module = $module;
    }

    public function initialise(AbstractConfig $template, $filePath) {
		$registry = System::getInstance()->getService(CoreServices::FACTORY_REGISTRY);
		foreach($template->getSections() as $sectName => $section) {
		    $page = $registry->newInstance(SystemFactories::PAGE,$section->getSetting('page_class'),array($sectName, $section, $this->_module));
            $page->setFilePath($filePath);
            $page->init($section);
            
            $this->add($page);
        }
    }

    public function rewind() {
        $this->position=0;
        $this->keys = array_keys($this->pages);
    }
    
    public function current() {
        return $this->pages[$this->keys[$this->position]];
        $this->position++;
    }
    public function next() {
        $this->position++;
    }
    public function valid() {
        return isset($this->keys[$this->position]);
    }

    public function key() {
        return $this->keys[$this->position];
    }

    function add(AbstractPage $page) {
        $pageName = $page->getName();
        if (!$pageName) throw new NullValueException("Page cannot be added to PageCollection because the name is null");
        $this->pages[$pageName]=$page;
        if (!in_array($pageName,$this->keys)) $this->keys[]=$pageName;
    }
    
    function has($name) {
        return isset($this->pages[$name]);
    }
    function get($name) {
            return isset($this->pages[$name]) ? $this->pages[$name] : null;
    }
    function combineWith(PageCollection $pages) {
        foreach($pages as $name => $page) {
			if($this->get($name)) throw new PageExistsException("Cannot combine PageCollections as page '$name' already exists in target");
            $this->add($page);
        }
    }
    
    function asArray() { 
        return $this->pages;
    }

}
