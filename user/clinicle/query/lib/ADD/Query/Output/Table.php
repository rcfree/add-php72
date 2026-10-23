<?php
;
abstract class ADD_Query_Output_Table extends ADD_Query_Output {
	protected $_result;
	protected $_rowPerPatient=false;
	protected $_fields=array();
	protected $_maxTypeCount=array();
	protected $_rowCount=0;
	protected $_fileName;
	protected $_fieldPositions=array();
	protected $_handle;
	protected $_maxRows;
	
	function setFileName($fileName) {
		$this->_fileName = $fileName;
	}
	function getFileName() {
		return $this->_fileName;
	}
	
	function beforeOutput(ADD_Query_Result $result) {
		if($this->_fileName) {
			$this->_handle = fopen($this->_fileName,"w");
		}
		else {
			$this->_handle = fopen("php://stdout","w");
		}
		if(!$this->_rowPerPatient) {
			$this->_fields = array("ID","Encounter Type");
		}
		$patientMap = $result->getPatientFieldMap();
		$fieldPos=0;
		foreach($patientMap as $patientName => $patientLabel) {
			$this->_fields[]=$patientLabel;
			$this->_fieldPositions[$patientName]=$fieldPos;
			$fieldPos++;
		}
		if($this->_rowPerPatient) return;
		$encounterMap = $result->getEncounterFieldMap();
		
		foreach($encounterMap as $encType => $encFields) {
			foreach($encFields as $obsName => $obsTitle) {
				$this->_fields[]=$obsTitle." (".$encType.")";
				$this->_fieldPositions[$encType.".".$obsName]=$fieldPos;
				$fieldPos++;
			}
		}
		$this->_outputHeadings($this->_handle);
	}
	
	function afterOutput(ADD_Query_Result $result) {
		if(!$this->_fileName) return;
			
		fclose($this->_handle);
		if(!$this->_rowPerPatient) return;
		
		$headingFile = fopen($this->_fileName.".headings","w");
		$this->_outputHeadings($headingFile);
		fclose($headingFile);
		
		system("cat ".$this->_fileName.".headings ".$this->_fileName." > /tmp/out && mv /tmp/out ".$this->_fileName);
		unlink($this->_fileName.".headings");
	}
	
	function getRowHeading($obsName,$obsTitle,$encType) {
		return $obsTitle;
	}
	protected function _outputRow($row) {	}
	
	function outputResultItem(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
		$patientRow = array();
		$patientMap = $result->getPatientFieldMap();

		foreach($patientMap as $patientName => $patientLabel) {
			$patientRow[]=$resultItem->getPatientData($patientName);
		}
		
		$encFieldMap = $result->getEncounterFieldMap();
		
		if ($this->_rowPerPatient) {
			$rows = $this->_getSingleDataRow($patientRow, $encFieldMap, $resultItem);
		}
		else {
			$rows = $this->_getMultipleDataRows($patientRow, $encFieldMap, $resultItem);
		}
		
		foreach($rows as $row) {
			$newRow = array();
			for($i=0;$i<count($this->_fields);$i++) {
				$newRow[]=ADD_Util::val($i,$row,"");
			}
			//print_r($newRow);
			$this->_outputRow($newRow);
			$this->_rowCount++;
			if($this->_maxRows && $this->_rowCount>=$this->_maxRows) {
				return true;
			}
		}
		return false;
	}
	
	function _getSingleDataRow($patientRow, $encFieldMap, ADD_Query_ResultItem $resultItem) {
		$row = $patientRow;
		foreach($encFieldMap as $encounterType => $encounterFields) {
			$encData = $resultItem->getEncounterData($encounterType);
			
			if($encData) {
				$this->_updateMaxTypeCount($encounterType,array_keys($encData));
				
				$ecounter=1;
				$prevFieldPos=0;
				foreach($encData as $id => $obsValues) {
	
					foreach($encounterFields as $encFieldName => $encFieldTitle) {
						$combinedFieldName = $encounterType.".".$encFieldName.".".$ecounter;
						if(!isset($this->_fieldPositions[$combinedFieldName])) {
							$this->_fields[]=$encFieldTitle." (".$encounterType.") $ecounter";
							$this->_fieldPositions[$combinedFieldName]=count($this->_fields)-1;
						}
						$fieldPos = $this->_fieldPositions[$combinedFieldName];
						$row[$fieldPos]=ADD_Util::val($encFieldName,$obsValues);
						
					}
					$ecounter++;
				}
				
			}
			
			$maxTypeCount = $this->_getMaxTypeCount($encounterType);
		}
		//ADD::log($this)->info("row:".json_encode($row));		
		return array($row);
	}
	function _getMultipleDataRows($patientRow, $encFieldMap, ADD_Query_ResultItem $resultItem) {
		$rows = array();
		
		$encData = $resultItem->getEncounterData();
			
		foreach($encFieldMap as $encounterType => $encounterFields) {
			$encTypeData = ADD_Util::val($encounterType,$encData);
			if(!$encTypeData) {
				continue;
			}

			foreach($encTypeData as $id => $obsValues) {
				$row = array_merge($patientRow,array_fill (0 , count($this->_fieldPositions) ,"" ));
				foreach($encounterFields as $encFieldName => $encFieldTitle) {
					$fieldPos = $this->_fieldPositions[$encounterType.".".$encFieldName];
					$row[$fieldPos]=isset($obsValues[$encFieldName]) ? $obsValues[$encFieldName] : null;
				}

				array_unshift($row,$encounterType);
				array_unshift($row,$id);
				
				$rows[]=$row;
			}
		}
		
		return $rows;
	}
    
    
	function _getPatientRow($patientMap,ADD_Query_ResultItem $resultItem) {
		$patientRow = array();
		foreach($patientMap as $patientName => $patientLabel) {
			$patientRow[]=$resultItem->getPatientData($patientName);
		}
		return $patientRow;
	}
	function getRowCount() {
		return $this->_rowCount;
	}
	function setMaxRows($maxRows) {
		$this->_maxRows = $maxRows;
	}
	function setRowPerPatient($rowPerPatient) {
		$this->_rowPerPatient = $rowPerPatient;
	}
	
	function _updateMaxTypeCount($type,$count) {
		if(is_array($count)) $count = count($count);
		if(!isset($this->_maxTypeCount[$type]) || $count>$this->_maxTypeCount[$type]) {
			$this->_maxTypeCount[$type]=$count;
			ADD::log($this)->info("max type count $type:".$count);
		}
	}
	function _getMaxTypeCount($type) {
		return ADD_Util::val($type,$this->_maxTypeCount,0);
	}
	function _outputHeadings($handle) {}
}