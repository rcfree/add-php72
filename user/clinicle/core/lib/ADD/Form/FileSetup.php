<?php
class ADD_Form_FileSetup {
	private $_fieldContainer;
	private $_currFieldNo=0;
	
	function __construct(ADD_Form_FieldContainerInterface $fieldContainer) {
		$this->_fieldContainer=$fieldContainer;
	}
	function _setupDataFile($formPath) {
		if (preg_match("/.csv$/",$formPath)) {
            $dataFile = new LRB_DataFile_Delimited($formPath);
            $dataFile->setHeaderRow(true);
        }
        elseif (preg_match("/.ftx$/",$formPath)) {
            $dataFile = new LRB_DataFile_FieldText($formPath);
            $dataFile->setHeaderRow(true);
        }
        else {
            $dataFile = new LRB_DataFile_Excel($formPath);
        }

		$dataFile->load();
		return $dataFile;
	}
	
	/**
	 * Prepare Form object with various child arrays and output the top level field names
	 * @return array $topLevelFieldNames
	 */
	function loadAndInit($formPath, $factoryName, $page=null) {
		$add = ADD::sys();
		$dataFile = $this->_setupDataFile($formPath);
        if(!$page) $page = $this->_fieldContainer;
		
		if ($this->_fieldContainer->areFieldsInit()) return;
		if (!$dataFile) throw new ADD_FormException("Cannot create Form fields. Form ".get_class($this->_fieldContainer)." expects a FormContainer decorator object");

		$this->_prepareFieldContainer($dataFile,$factoryName,$page);
		
		$this->_fieldContainer->setFieldsInit(true);
	}
	 function _prepareFieldContainer($dataFile,$factoryName, $page) {
		 $dataItems = $dataFile->getDataItems();

        $registry = ADD::sys()->getService(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY);
		
		if($factoryName=="ReportData") {
			$factoryConst = \ClinicLE\System\Factory\SystemFactories::REPORT_FIELD;
		}
		elseif($factoryName=="Database") {
			$factoryConst = \ClinicLE\System\Factory\SystemFactories::DB_FIELD;
		}
		else {
			$factoryConst = \ClinicLE\System\Factory\SystemFactories::ROW_FIELD;
		}
		$ff = $registry->getFactory($factoryConst);
            
        if ($dataItems) {
			foreach($dataItems as $dataItem) {
                          
				$field = $ff->newInstance($dataItem);

				if ($field->getName()) $this->_fieldContainer->addField($field);
				$field->setup($this->_fieldContainer);
			}
			$this->_fieldContainer->prepareChildrenAndTopLevelFields();
			if (!$this->_fieldContainer->doNotFinalise()) {
				foreach($this->_fieldContainer->getFieldsByName() as $name => $field) {
                    $field->finaliseField($page);
				}
			}
		}	
	 }
	 
	
}
?>
