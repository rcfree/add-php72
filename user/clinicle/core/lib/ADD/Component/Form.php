<?php

class ADD_Component_Form extends ADD_Component implements ADD_Form_FieldContainerInterface {
	
	protected $_fieldContent = null;
	private $_uncleanValues = array();
	private $_generatedFieldNames=array();
	protected $_formAction=null;
	private $ignoreNg;

	function __construct(ADD_Page $page=null) {
		parent::__construct($page);
		$this->_fieldContent = new ADD_Form_FieldContent();
	}
	function reset() {
		$this->_fieldContent = new ADD_Form_FieldContent();
		$this->setSetup(false);
		$this->setup();
	}
	function getFieldContent() {
		return $this->_fieldContent;
	}
	function beforeInstall() {
		foreach ($this->getFields() as $field) {
			$field->beforeInstall($this);
		}
	}
	function getTopLevelFields() {
		return $this->_fieldContent->getTopLevelFields();
	}
	function addGeneratedFieldName($fieldName) {
            $this->_generatedFieldNames[$fieldName]=true;
        }
        function hasGeneratedFieldName($fieldName) {
            return isset($this->_generatedFieldNames[$fieldName]);
        }
        function getGeneratedFieldNames() {
            return $this->_generatedFieldNames;
        }
	function populate() {
        $page = $this->_page;
		$data = $page->getData();
		
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			$field->beforePopulateFields($page);
		}
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			$field->populateValue($data,$page);
		}
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			$field->afterPopulateFields($page);
		}
	}
	
	public function getNextField($searchField) {
		$this->_fieldContent->getNextField($searchField);
	}

	public function setChildFieldsByName($childFieldsByName) {
		$this->_fieldContent->setChildFieldsByName($childFieldsByName);
	}
        public function addChildField($parentFieldName,$childField) {
		$this->_fieldContent->addChildField($parentFieldName,$childField);
	}
        
	function setParentFieldsByName($parentFieldsByName) {
		$this->_fieldContent->setParentFieldsByName($parentFieldsByName);
	}

	function getFields() {
		return $this->_fieldContent->getFields();
	}

	function getChildFieldsByName() {
		return $this->_fieldContent->getChildFieldsByName();
	}

	function getParentFieldsByName() {
		return $this->_fieldContent->getParentFieldsByName();
	}

	public function getParentField($name) {
		return $this->_fieldContent->getParentField($name);	
	}

	public function getChildFields($fieldName) {
		return $this->_fieldContent->getChildFields($fieldName);	
	}

	function getFieldsByName() {
		return $this->_fieldContent->getFieldsByName();	
	}

	function getField($fieldName) {
		return $this->_fieldContent->getField($fieldName);	
	}
	function getUncleanValues() {
		return $this->_uncleanValues;
	}
	function setUncleanValues($uncleanValues) {
		$this->_uncleanValues=$uncleanValues;
	}
	function getUncleanValue($key) {
		return isset($this->_uncleanValues[$key]) ? $this->_uncleanValues[$key] : null;
	}
	function addField($field) {
            return $this->_fieldContent->addField($field);	
	}
	
	function unshiftField($field) {
		return $this->_fieldContent->unshiftField($field);	
	}
	
	function areFieldsInit() {
		return $this->_fieldContent->areFieldsInit();
	}
	function setFieldsInit($fieldsInit) {
		return $this->_fieldContent->setFieldsInit($fieldsInit);
	}
	
	function generate() {
            $page = $this->_page;
            $topLevelFields = $this->_fieldContent->getTopLevelFields();
            foreach($topLevelFields as $fieldName) {
                    $field = $this->_fieldContent->getField($fieldName);
                    $field->createMarkup($page);
            }

            foreach($topLevelFields as $fieldName) {
                    $field = $this->_fieldContent->getField($fieldName);
                    $field->afterAddFields($page);
            }
	}

	/**
	 * Obtain child fields from particular position in Form's array of fields
	 * @param number $position
	 * @return multitype:Ambigous <number:string, multitype:number unknown >
	 */
	function getChildFieldAtPosition($position) {
		return $this->_fieldContent->getChildFieldAtPosition($position);
	}

	function prepareChildrenAndTopLevelFields() {
		return $this->_fieldContent->prepareChildrenAndTopLevelFields();
	}

	function beforeSubmit(&$values,$origValues=null) {
            $page = $this->_page;
			
            list($cleanValues,$uncleanValues,$notDefined) = $this->cleanValues($values, $page);
            $this->setUncleanValues($uncleanValues);
            $values = $cleanValues;
	}
	
	function cleanValues($values) {
		$cleanValues = array();
		$uncleanValues = array();
                
                $page = $this->_page;

		foreach($values as $key => $value) {
			if (preg_match("/^__/",$key)) {
				$uncleanValues[$key]=$values[$key];
				unset($values[$key]);
			}
		}
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			if ($field->isStored()) {
				if (method_exists($field,"cleanValue")) {
					$cleanValues[$name] = $field->cleanValue($values);
				}
				else {
					if (isset($values[$name])) $cleanValues[$name]=$values[$name];
				}
			}
			else {
				if (isset($values[$name])) $uncleanValues[$name]=$values[$name];
			}
			$field->removeFieldFromValues($values);
			
			$field->beforeSubmit($cleanValues,$page);
		}
		
		$notDefined=array_keys($values);
		return array($cleanValues,$uncleanValues, $notDefined);
	}

	function setIgnoreNg($ignoreNg) {
	    $this->ignoreNg = $ignoreNg;
    }
	function beforeAllGenerate() {
		$page = $this->_page;

		$method = $this->getFormMethod();
		$action = $this->getFormAction();
		$attrs = array("method"=>$method,"id"=>"content-form","onsubmit"=>$this->ignoreNg ? "return true" :
            "return false"); //ADD.Form
        //.isValid()
        //;");
		if($action) $attrs['action']=$action;
		$page->html()->startElement("form",$attrs,"form_start");
		$page->html()->addElement("input",array("value"=>$page->getModule()->getAlias().":".$page->getName(),"name"=>"__pim","type"=>"hidden"));
	}
	function getFormMethod() {
		return $this->_page->getSetting('method',"post");
	}
	function getFormAction() {
		return $this->_formAction;
	}
	function afterAllGenerate() {
        $page = $this->_page;
		$form = $page->getComponent('Form');

		foreach($form->getFields() as $field) {
			$field->afterAllGenerate($page);
			
			if (!$field->isHidden()) continue;
			$parentField = $form->getParentField($field);
			if ($parentField && $parentField->isHidden()) continue;

			$jqSel = $field->generateJQueryBlockSel($page);
		}
		$page->html()->endElement("form","form_end");
	}
	
	function beforeGenerate() {
                $page = $this->_page;
		$token = ADD_Util::token();
		ADD::sys()->setState('token',$token);
		$page->html()->addElement('input',array('type'=>'hidden','value'=>$token,'name'=>ADD_Component::TOKEN));
 	}
	
	function finaliseForm() {
		foreach($this->getFieldsByName() as $name => $field) {
				$field->finaliseField($this->_page);
		}
	}
}
?>