<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Common\Factory;
use ClinicLE\Common\Exception\ClassLoadException;
use ReflectionClass;

/**
 * Description of ClassRegister
 *
 * @author rcf8
 */
abstract class AbstractFactory {
	private $classType;
	private $defaultClassName;
	private $classMap = array();
	
	private $registry;
	
	public function __construct(FactoryRegistry $registry) {
		$this->registry = $registry;
	}
	public function initNamespaces() {

	}
	private function findClassFullName($classPath) {
		$classNames = array();
		foreach ($this->registry->getNamespaces() as $namespace) {
			$classFullName = $namespace . "\\" . $classPath;

			if (!class_exists($classFullName)) {
				continue;
			}
			$classNames[] = $classFullName;
		}
		if (count($classNames) > 1) {
			throw new ClassLoadException("'" . $classPath . "' was found in multiple namespaces: " . implode(";", $classNames));
		}
		return isset($classNames[0]) ? $classNames[0] : null;
	}
	
	public function preProcessArguments(&$arguments) {
		
	}
	public function newInstance($classLabel, $arguments=array()) {
		$this->preProcessArguments($arguments);
		$compiledClassName = $this->registry->getCompiledClassName($this->getClassType(), $classLabel);
        if($compiledClassName!==null) {
            return $this->createInstance($compiledClassName, $arguments);
        }

		if(empty($classLabel)) {
			$defaultClassName = $this->getDefaultClassName();
			if(!$defaultClassName) {
				throw new \InvalidArgumentException("Null class name and no default set in ".get_class($this));
			}
			return $this->createInstance($this->getDefaultClassName(),$arguments);
		}
		$classFullName = $this->searchMap($classLabel);
		if(!$classFullName) {
			$classFullName = $this->searchNamespaces($classLabel);
		}
        if(!$classFullName) {
            $classFullName = $this->searchLegacy($classLabel);
        }

        if(!$classFullName) {
			throw new ClassLoadException("Could not create a new ".$this->getClassType()." instance with name '" . $classLabel . "'");
		}
		$this->registry->putCompiledClassName($this->getClassType(), $classLabel,$classFullName);
		return $this->createInstance($classFullName, $arguments);
	}
	protected function createInstance($classFullName,$arguments) {
		$refl = new ReflectionClass($classFullName);
		if($refl->hasMethod("__construct")) {
			if($arguments===null) {
			    $arguments = array();
            }
			$class = $refl->newInstanceArgs($arguments);
		}
		else {
			$class = $refl->newInstanceArgs(array());
		}
		return $this->processInstanceWithArgs($class, $arguments);
	}
	public function searchMap($classLabel) {
		if(isset($this->classMap[$classLabel])) {
			return $this->classMap[$classLabel];
		}
		return null;
	}
	public function searchLegacy($classLabel) {
		$legacyClassTypes = is_array($this->getLegacyClassType()) ? $this->getLegacyClassType() : array($this->getLegacyClassType());
		
		foreach($legacyClassTypes as $lcType) {
			$className = "ADD_".$lcType."_".$classLabel;
			if(class_exists($className)) {
				return $className;
			}
		}
	}
	public function searchNamespaces($classLabel) {
		$classPath = $this->getClassType() . "\\" . $classLabel . $this->getClassType();

		return $this->findClassFullName($classPath);
	}
	function getClassType() {
		return $this->classType;
	}
	
	function getLegacyClassType() {
		return $this->classType;
	}

	function setClassType($classType) {
		$this->classType = $classType;
	}	
	function getDefaultClassName() {
		return $this->defaultClassName;
	}

	function setDefaultClassName($defaultClassName) {
		$this->defaultClassName = $defaultClassName;
	}
	function processInstanceWithArgs($class,$originalArgs) {
	    return $this->processInstance($class);
    }
	function processInstance($class) {
		return $class;
	}
	function addClassItem($label,$className) {
		$this->classMap[$label]=$className;
	}
	function addClassMap($classMap) {
		$this->classMap = $this->classMap + $classMap;
	}
	function addNamespace($namespace) {
		$this->registry->addNamespace($namespace);
	}
	function store() {
	    $this->factoryRegistry->store($this);
    }
}
