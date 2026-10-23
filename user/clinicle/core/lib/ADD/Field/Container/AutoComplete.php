<?php

class ADD_Field_Container_AutoComplete extends ADD_Field_Container {
	private $acField = null;
	private $valueField = null;
	protected $stored = true;
    private $populated=array();
        public function ignoreContainerTest() {
            return true;
        }
        public function newOptionInstance() {
            $dictionaryOption = $this->getSetting('dictionary_option',"DictionaryItems");

            $options = ADD_Util::loadClass("ADD_Field_Option_".$dictionaryOption);
            if(method_exists($options,"setDictionaryName")) $options->setDictionaryName($this->getSetting('dictionary'));
            return $options;
        }
        
        public function getDisplayText($form,$id) {
            $value = $this->getValue();
            $options = $this->newOptionInstance();

            if ($value) {
                $optLabel = $options->findLabelWithValue($value);
                return $optLabel ? $optLabel : "Option '$value' not present";
            }
            else {
                return "- No option selected -";
            }
	}
	
        function finaliseField($page) {
            parent::finaliseField($page);
            $fieldName = $this->getName();
            $form = $page->getComponent('Form');

            if(method_exists($form,"addGenerateFieldName")) {
                $form->addGeneratedFieldName("__" . $fieldName . "_ac");
            }
        }
                
	function beforeAddMarkup($page) {
		$level = $this->getLevel();
		$fieldName = $this->getName();
		$fieldData = $this->fieldData;
		
		//create option Field object of type specified in 'cond_option_type' setting
		$fieldData['Field']="__".$fieldName."_ac";
		$fieldData['Type']="text";
		$fieldData['Level']=$level+1;
        $ff = new ADD_FieldFactory_RowData();

        $acField = $ff->newInstance($fieldData);
		$acField->finaliseField($page);
		$this->acField = $acField;
			
		$valueData = array(
				'Field'=>$fieldName,
				'Type'=>'hidden',
				'Title'=>'',
				'Options'=>'',
				'Suffix'=>'',
				'Level' => $level+1,
				'Settings' => $this->getSettings()
		);
	   $valueField = $ff->newInstance($valueData);
		$valueField->finaliseField($page);
		$this->valueField = $valueField;

        if(!$this->getSetting("db_populated")) {
            return;

        }
        $options = $this->newOptionInstance();
        $this->valueField->populateValue($this->populated[0],$this->populated[1],$this->populated[2]);
        if(!ADD_Util::isEmpty($this->valueField->getValue())) {
            $this->acField->setValue($options->findLabelWithValue($this->valueField->getValue()));
        }
	}

	function addMarkup($page) {
		$add = ADD::sys();
		if($add->hasState('readOnly') && !$this->getSetting("show_if_readonly")) {
		    return;
        }
		$db = ADD::db('dictionary');
        $dictionary = $this->assertSetting('dictionary');
        $dictionaryOption = $this->getSetting('dictionary_option');
                
		if (!$db->hasDictionary($dictionary)) {
			throw new ADD_FieldException("AutoComplete Field '".$this->getName()."' defines a dictionary '".$this->getSetting('dictionary')."' that does not exist");		
		}
		$fieldName = $this->getName();
		$html = $page->html();
		
		
		$html->startElement("div",array("no-view"=>$this->getSetting('no_view')));
		//$this->acField->setValue("Asthma");
                //$this->valueField->setValue("J45");
		$this->acField->createMarkup($page);
		if ($this->getSetting('show_list') && !$add->hasState('readOnly')) {
			
			$attrs = array(
				"style"=>"position:relative;left:-30px;top:-5px",
				"class"=>"btn btn-link",
				"type"=>"button",
				"onclick"=>"toggleAc('{$fieldName}')"
			);
			
			$html->addBeforeElement("__".$fieldName."_ac_end",array("text"=>"<i class='icon-chevron-down'></i>","tag"=>"button","type"=>ADD_Content_Html::TEXT,"attrs"=>$attrs));
			//$html->endElement("a");
		}

        $this->valueField->createMarkup($page);

        $dataFields = array();
		
		foreach($this->getSetting('data_field',array()) as $dataField) {
			list($data,$field)=explode(":",$dataField);
			$dataFields[]=array('source'=>$data,'target'=>$field);
		}
		$clearText = $this->getSetting('clear_text',0);
		$minLength = $this->getSetting('min_search',2);
		$mustExist = $this->getSetting('must_exist',0);
                $words = $this->getSetting('words',0);
                
                $createFunction = $this->getSetting('create_function',null);
				$fromStart = $this->getSetting('from_start',"null");
		$page->addJavascript("autocomplete('".$this->getName()."','".$dictionary.":$dictionaryOption',$minLength, ".json_encode($dataFields).",$clearText,$mustExist, ".($createFunction ? "'".$createFunction."'" : "null").",$words,$fromStart);");
		
		$html->endElement("div");
	}
	
	public function removeFieldFromValues(&$values) {
		parent::removeFieldFromValues($values);
		if (isset($values["__".$this->getName()."_ac"])) unset($values["__".$this->getName()."_ac"]);
	}

    function populateValue($values, $page, $ignoreNew=false) {
        if(!$this->getSetting("db_populated")) {
            return;
        }
        $this->populated=array($values,$page,$ignoreNew);
    }

}
?>