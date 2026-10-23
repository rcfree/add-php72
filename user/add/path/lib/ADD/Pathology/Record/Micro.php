<?php




class ADD_Pathology_Record_Micro extends ADD_Pathology_Record_Base {
	private $_sens=array();
	private $_organisms=array();
	protected $_mapping=array (
	  'UNIQUE_TEST_ID' => 'unique_test_id',
	  'SPECIMEN_NUMBER' => 'specimen_no',
	  'SAMPLE_COLLECTION_DATE_TIME' => 'coll_date_time',
	  'LOCAL_TEST_CODE' => 'test_code',
	  'SPECIMEN_TYPE_CODE' => 'specimen_type_code',
	  'SPECIMEN_TYPE'=>'specimen_type',
	  'RESULT_CODE'=>'result_code',
	  'RESULT' => 'result',
	);
	
	function _populateValuesWithOtherData(&$obsValues) {
		if($this->_organisms) $obsValues['organisms']=$this->_organisms;
	}
	
	function _updateOrganism($organismType=null,$quantifier,$abiotic=null,$sens,$suppFlagInterp, $orgAttr, $orgDispLine) {
		$log = ADD::log($this);
			
		if(ADD_Util::isEmpty(trim($organismType)) && (ADD_Util::isEmpty(trim($quantifier)) || $quantifier=="B") && ADD_Util::isEmpty(trim($abiotic))) return;	
		if(!$organismType || strlen(trim($organismType))==0) $organismType="Not specified";
		$isolateKey = $organismType."||".$quantifier."||".$orgAttr."||".$orgDispLine;
		
		$this->setIsolateOrganism($isolateKey,$organismType);
		
		$this->setIsolateQuantifier($isolateKey,$quantifier);
		
		if(!$abiotic) return;
		
		if($suppFlagInterp=="Y") {
			$log->info("Suppression flag 'Y' - not added $organismType $quantifier $sens");
			return;
		}
		$this->setIsolateSensitivity($isolateKey,$abiotic,$sens);
	}
	
	private function setIsolateOrganism($isolateKey,$organismType) {
		if(!isset($this->_organisms[$isolateKey])) {
			$this->_organisms[$isolateKey]=array("organism"=>$organismType,"sens"=>array());
			$this->_changed=true;
		}
	}
	
	private function setIsolateQuantifier($isolateKey,$quantifier) {
		if(!$quantifier) return;
		
		if(!isset($this->_organisms[$isolateKey]['quantifier']) || $this->_organisms[$isolateKey]['quantifier']!=$quantifier) {
			$this->_organisms[$isolateKey]['quantifier']=$quantifier;
			$this->_changed=true;
		}
	}
	private function setIsolateSensitivity($isolateKey,$abiotic,$sens) {
		$sensList = $this->_organisms[$isolateKey]['sens'];
		
		if(isset($sensList[$abiotic]) && $sensList[$abiotic]!=$sens) {
            ADD::log($this)->warn("Duplicate sensitivity '$sens' in '$isolateKey' does not have the same result (".print_r($sensList[$abiotic],1));
            $sensList[$abiotic] = array("U");
		}
		
		if(isset($sensList[$abiotic])) return;
		
		//if($this->_organisms[$isolateKey]['sens'][$sens]
		
		$this->_organisms[$isolateKey]['sens'][$abiotic]=$sens;
		$this->_changed=true;
	}
	
	function _getOrganisms() {
		return $this->_organisms;
	}
		
	function updateFromRowData($rowData) {
		$this->_updateRowData($rowData);
		$quantifier = ADD_Util::val('QUANTIFIER',$rowData);
		$organism = ADD_Util::val('ORGANISM',$rowData);
		$antibiotic = ADD_Util::val('ANTIBIOTIC_CODE',$rowData);
		$sensResult = ADD_Util::val('INTERPRETED_SENSITIVITY_RESULT',$rowData);
		$suppFlagInterp = ADD_Util::val('SUPPRESSION_FLAG_INTERPRETED',$rowData);
        $orgAttr = ADD_Util::val('ORGANISM_ATTRIBUTE',$rowData);
        $orgDispLine = ADD_Util::val('ORGANISM_DISPLAY_LINE',$rowData);
		$this->_updateOrganism($organism,$quantifier,$antibiotic,$sensResult,$suppFlagInterp, $orgAttr, $orgDispLine);
	}

	function _updateOtherDataFromValues($obsValues) {
		$organisms = ADD_Util::val('organisms',$obsValues);
		if($organisms) {
			$this->_organisms=json_decode($organisms,true);
		}
	}
}