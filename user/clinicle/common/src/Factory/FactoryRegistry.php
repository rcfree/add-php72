<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Registry
 *
 * @author rcf8
 */
namespace ClinicLE\Common\Factory;
use ReflectionClass;

class FactoryRegistry {
	private $namespaces = array();
	private $compiledClassNames = array();
    private $storage;
    private $registryChanged = false;
    private $factories = array();

    /**
     * @param array $compiledClassNames
     */
    public function setCompiledClassNames($compiledClassNames)
    {
        $this->compiledClassNames = $compiledClassNames;
    }

    /**
     * @return RegistryStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getCompiledClassNames()
    {
        return $this->compiledClassNames;
    }

    public function __construct(RegistryStorageInterface $storage) {
        $this->storage = $storage;
    }

    /**
     * @return boolean
     */
    public function hasRegistryChanged()
    {
        return $this->registryChanged;
    }

    /**
     * @param boolean $registryChanged
     */
    public function setRegistryChanged($registryChanged)
    {
        $this->registryChanged = $registryChanged;
    }

    function addFactory($name,$factoryClass) {
        if(is_object($factoryClass)) {
            $factory = $factoryClass;
        }
        else {
            $refl = new ReflectionClass($factoryClass);
            $factory = $refl->newInstance($this);

        }
        $factory->initNamespaces();
		$this->factories[$name]=$factory;
        return $this;
	}
	
	public function addNamespace($namespace) {
		if (in_array($namespace, $this->namespaces)) {
			return;
		}
		$this->namespaces[] = $namespace;
	}
	
	public function getNamespaces() {
		return $this->namespaces;
	}
	
	public function getFactory($name) {
		return $this->factories[$name];
	}
	
	public function hasFactory($name) {
		return isset($this->factories[$name]);
	}
	
	public function newInstance($factoryName, $classLabel, $arguments=array()) {
		return $this->getFactory($factoryName)->newInstance($classLabel,$arguments);
	}

	public function getCompiledClassName($classType, $classLabel) {
	    if(isset($this->compiledClassNames[$classType][$classLabel])) {
	        return $this->compiledClassNames[$classType][$classLabel];
        }
        return null;
    }
    public function putCompiledClassName($classType, $classLabel, $compiledClassName) {
        if(!isset($this->compiledClassNames[$classType])) {
            $this->compiledClassNames[$classType]=array();
        }

        $this->compiledClassNames[$classType][$classLabel]=$compiledClassName;
        $this->registryChanged = true;

    }
}
