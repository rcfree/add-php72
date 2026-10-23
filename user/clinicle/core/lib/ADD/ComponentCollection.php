<?php

class ADD_ComponentCollection implements Iterator {
    private $_position=0;
    private $_components = array();
    private $_page = null;
    private $_keys = array();

    public function __construct(ADD_Page $page) {
        $this->_page = $page;

    }
    public function callFunction($functionName,$args=array()) {
        foreach($this->_components as $name=>$component) {
            call_user_func_array(array($component,$functionName),$args);
        }
        return $args;
    }
    public function callFilteredFunction($functionName,$args=array(),$filterInstanceOf=null) {
        foreach($this->_components as $name=>$component) {
            if ($filterInstanceOf && !($component instanceof $filterInstanceOf)) continue;
            call_user_func_array(array($component,$functionName),$args);
        }
        return $args;
    }
    public function initialise() {
        $compNames = $this->_page->getSettingList('components');

        foreach($compNames as $compName) {
            $comp = $this->add($compName);
            
            foreach($comp->getComponentList() as $clName) {
                $this->add($clName);
            }
        }
    }
    
    public function rewind() {
        $this->_position=0;
        $this->_keys = array_keys($this->_components);
    }
    
    public function current() {
        return $this->_components[$this->_keys[$this->_position]];
    }
    public function next() {
        $this->_position++;
    }
    public function valid() {
        return isset($this->_keys[$this->_position]);
    }

    public function key() {
        if(count($this->_keys)==0) return null;
        return $this->_keys[$this->_position];
    }
    
    function add($componentOrName) {
        $registry = ADD::sys()->getService(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY);
        if(!is_object($componentOrName)) {
			$component = $registry->newInstance(\ClinicLE\System\Factory\SystemFactories::COMPONENT,$componentOrName);
		}
		else {
			$component = $componentOrName;
		}
        $componentName = $component->getType();
        //list($componentName, $component) = $this->determineObjectAndName($componentOrName);
        $this->addBasedOnFirstPartOfName($component, $componentName);

        $component->setPage($this->_page);
		$component->init();
        return $component;
    }
    function has($name) {
        return isset($this->_components[$name]);
    }
    function get($name) {
        if (!isset($this->_components[$name])) throw new ADD_ComponentNotInCollectionException("Component '$name' does not exist in the ComponentCollection of page '".$this->_page->getName()."'");
        return $this->_components[$name];
    }
    
    /**
     * @param ADD_Component $component
     * @return array
     */
    private function determineObjectAndName($componentOrName)
    {
        if (is_object($componentOrName)) {
            $component = $componentOrName;
            $componentName = str_replace("ADD_Component_", "", get_class($component));
        } else {
            $componentName = $componentOrName;
            $component = ADD_Util::loadClass("ADD_Component_$componentName");
        }
        return array($componentName, $component);
    }

    /**
     * @param ADD_Component $component
     * @param $componentName
     */
    private function addBasedOnFirstPartOfName(ADD_Component $component, $componentName)
    {
        $componentDomains = explode("_", $componentName);
        $this->_components[$componentDomains[0]] = $component;
        $this->_keys[]=$componentDomains[0];
    }
    
    function asArray() { 
        return $this->_components;
    }
}
