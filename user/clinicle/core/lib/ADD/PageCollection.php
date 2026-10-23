<?php
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Module\Module;
use ClinicLE\Common\Config\AbstractConfig;

class ADD_PageCollection implements Iterator {
    private $_position=0;
    private $_pages = array();
    protected $_module = null;
    private $_keys = array();
    
    public function __construct(Module $module) {
        $this->_module = $module;
    }

    public function initialise(AbstractConfig $template, $filePath) {
		$registry = ADD::sys()->getService(CoreServices::FACTORY_REGISTRY);
		foreach($template->getSections() as $sectName => $section) {
            $page = $registry->newInstance(SystemFactories::PAGE,$section->getSetting('page_class'),array($sectName, $section, $this->_module));
            $page->setFilePath($filePath);
            $page->init($section);
            
            $this->add($page);
        }
    }

    public function rewind() {
        $this->_position=0;
        $this->_keys = array_keys($this->_pages);
    }
    
    public function current() {
        return $this->_pages[$this->_keys[$this->_position]];
        $this->_position++;
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

    function add($page) {
        $pageName = $page->getName();
        if (!$pageName) throw new ADD_NullValueProvided("Page cannot be added to PageCollection because the name is null");
        $this->_pages[$pageName]=$page;
        if (!in_array($pageName,$this->_keys)) $this->_keys[]=$pageName;
    }
    
    function has($name) {
        return isset($this->_pages[$name]);
    }
    function get($name) {
            return isset($this->_pages[$name]) ? $this->_pages[$name] : null;
    }
    function combineWith(ADD_PageCollection $pages) {
        foreach($pages as $name => $page) {
			if($this->get($name)) throw new ADD_PageExistsException("Cannot combine PageCollections as page '$name' already exists in target");
            $this->add($page);
        }
    }
    
    function asArray() { 
        return $this->_pages;
    }

}
