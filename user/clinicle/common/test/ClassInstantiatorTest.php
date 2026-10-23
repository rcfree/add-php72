<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Common;

class ClassInstantiatorTest extends \PHPUnit_Framework_TestCase {
    private $SUT;
    const CLASSTYPE = "Component";
    const CLASSTYPE2 = "Field";
    const NONS_CLASS = "NoNamespace";
    const EXISTING_CLASS = "Existing";
    const DUPLICATE_CLASS = "Duplicate";
    const LATER_CLASS = "LaterThree";
    
    public function setup() {
	$this->SUT = new ClassInstantiator();
    }
    
    /**
     * @test
     */
    function it_should_fail_if_no_namespaces_have_been_defined() {
	$this->setExpectedException("ClinicLE\\Common\\Exception\\NullValueException","'".self::NONS_CLASS.self::CLASSTYPE."' could not be defined as there are no namespaces defined in ClassInstantiator");
	$this->SUT->instantiate(self::CLASSTYPE, self::NONS_CLASS);
    }
    
    /**
     * @test
     */
    function it_should_instantiate_a_class_in_a_single_namespace() {
	$this->SUT->setNamespaces(array("ClinicLE\\NamespaceOne"));
	$class = $this->SUT->instantiate(self::CLASSTYPE, self::EXISTING_CLASS);
	$this->assertInstanceOf("ClinicLE\\NamespaceOne\\".self::CLASSTYPE."\\".self::EXISTING_CLASS.self::CLASSTYPE,$class);
    }
    
    /**
     * @test
     */
    function it_should_fail_if_a_class_has_a_duplicate_name_in_a_different_namespace() {
	$this->setExpectedException("ClinicLE\\Common\\Exception\\ClassLoadException","'".self::DUPLICATE_CLASS.self::CLASSTYPE."' was found in multiple namespaces");
	
	$this->SUT->addNamespace("ClinicLE\\NamespaceOne");
	$this->SUT->addNamespace("ClinicLE\\NamespaceTwo");
	$this->SUT->instantiate(self::CLASSTYPE, self::DUPLICATE_CLASS);
    }
    
    /**
     * @test
     */
    function it_should_instantiate_a_class_which_is_not_in_the_first_namespace() {
	$this->SUT->setNamespaces(array("ClinicLE\\NamespaceOne","ClinicLE\\NamespaceTwo","ClinicLE\\NamespaceThree","ClinicLE\\NamespaceFour"));
	
	$class = $this->SUT->instantiate(self::CLASSTYPE2, self::LATER_CLASS);
	print_r($class);
	$this->assertInstanceOf("ClinicLE\\NamespaceThree\\".self::CLASSTYPE2."\\".self::LATER_CLASS.self::CLASSTYPE2,$class);
    }
    
}


namespace ClinicLE\NamespaceOne\Component;
class DuplicateComponent {
    
}
class ExistingComponent {
    
}
namespace ClinicLE\NamespaceTwo\Component;
class DuplicateComponent {
    
}
namespace ClinicLE\NamespaceOne\Field;
class LaterOneField {
    
}
namespace ClinicLE\NamespaceTwo\Field;
class LaterTwoField {
    
}
namespace ClinicLE\NamespaceThree\Field;
class LaterThreeField {
    
}