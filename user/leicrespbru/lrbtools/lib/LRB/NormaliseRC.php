<?php
require_once("LRB/DataFile/Base.php");
require_once("LRB/DataFile/Delimited.php");
	
class LRB_NormaliseRC {
	private $idField;
	private $patientData=array();
	private $fieldNames=array();
	private $eventFieldNames = array("redcap_event_name","Event Name");
	
	function getFieldNames() {
		return array_keys($this->fieldNames);
	}
	static function createFromFileHeadings($csv_filename) {
		$output = array();
                exec("cut -f1 ".$csv_filename." -d,",$output);
                $study_id = trim($output[0],"\"");

                $output=array();
                exec("cut -f2 ".$csv_filename." -d,",$output);
                $event_name = trim($output[0],"\"");

                error_log("study_id:$study_id, event_name:$event_name");
		return new LRB_NormaliseRC($csv_filename,$study_id,$event_name);
	}
	function __construct($fileName, $idField='study_id', $eventField='redcap_event_name') {
		$this->idField=$idField;
		$this->eventField=$eventField;
		$this->fileName = $fileName;
	}

	function outputNormalisedFile() {	
		if($this->hasNoEvents()) return;
		
		try {
		$this->dataFile = new LRB_DataFile_Delimited($this->fileName);

                $this->dataFile->setHeaderRow(true);
                $this->dataFile->setLimit(1000);
		$this->dataFile->open();

		$headings = $this->dataFile->getHeadings();
		$this->fieldNames=array($this->idField=>true);
		
		#load file
		while($row = $this->dataFile->nextRow()) {
			$this->processRow($row);
		}
		$outputFileName=$this->getOutputFileName();
		$this->outputFile = fopen($outputFileName,"w");
		
		$this->outputHeadings();
		$this->outputRowData();
		
		fclose($this->outputFile);
		error_log("outputFileName:$outputFileName");
		}
		catch(Exception $ex) {
			error_log($ex->getMessage());
			return $ex->getMessage();
		}
	}
	
	function getOutputFileName() {
		$pathInfo = pathinfo($this->fileName);
                $outputFileName=$pathInfo['dirname']."/".$pathInfo['filename'].".".$pathInfo['extension'].".full";
		return $outputFileName;
	}

	function hasNoEvents() {
		if(in_array($this->eventField,$this->eventFieldNames)) {
			return false;
		}
		error_log("copy file as it has no events");
		exec("cp ".$this->fileName." ".$this->getOutputFileName());
		return true;
	}	
		
		
			
	function outputHeadings() {
		$fieldNames = $this->getFieldNames();
		fputcsv($this->outputFile, $fieldNames, ",","\"");
	}
	function outputRowData() {
		$fieldNames = $this->getFieldNames();
		foreach($this->patientData as $studyId => $rowData) {
			$singleRow = array();	
			foreach($fieldNames as $fieldName) {
				$singleRow[]=isset($rowData[$fieldName]) ? $rowData[$fieldName] : "";
			}
			fputcsv($this->outputFile,$singleRow,",","\"");
		}	
	}
	
	function processRow($row) {
		if(!isset($row[$this->eventField])) {
			$eventName="";
		}
		else {
			$eventName = $row[$this->eventField];
		}
		$studyId = $row[$this->idField];
/*
		if (preg_match("/(_arm_\d+)$/",$eventName,$matches)) {
			$eventName = preg_replace("/_arm_\d+$/","",$eventName);
			$studyId.=$matches[1];
		}

 * 
 */		if(!isset($this->patientData[$studyId])) $this->patientData[$studyId]=array();
		$this->patientData[$studyId][$this->idField]=$studyId;
		foreach($row as $field => $value) {
			if($field==$this->idField || $field==$this->eventField) continue;
			if($value!='0' && ($value===null || strlen($value)==0)) continue;

			$fieldName = $eventName."_".$field;
			$this->fieldNames[$fieldName]=true;
			if(isset($this->patientData[$studyId][$fieldName])) {
				 throw new Exception("Field '$fieldName' is repeated for recruit '$studyId':\n".json_encode($row));
			}
			$this->patientData[$studyId][$fieldName]=$value;
		}
	}
}
?>
