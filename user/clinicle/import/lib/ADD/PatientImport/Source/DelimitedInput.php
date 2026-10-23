<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_PatientImport_Source_DelimitedInput {
	private $_inputFileNames;
	private $_inputFile;
	
	function prepare(ADD_PatientImport $patientImport) {
		$log = ADD::log($this);
		$context = $patientImport->getContext();
		$config = $context->getConfig();
		$inputFileName = $config->getSetting('inputFile');
		
		$inputDir = $config->getSetting('inputDir');
		
		if(!$inputFileName && !$inputDir) throw new Exception("'inputFile' and 'inputDir' missing from config");
		
		if($inputDir) {
			$log->info("Input dir is:".$inputDir);
			$fileNames = scandir($inputDir);
			$inputFileNames = array();
			foreach($fileNames as $fileName) {
				$fullPath = $inputDir."/$fileName"; 
				if (is_dir($fullPath)) continue;
				if (preg_match('/^\./',$fileName) || $fileName=='.' || $fileName=='..') continue;
				$inputFileNames[]=$fullPath;
			}
			$this->_inputFileNames=$inputFileNames;
		}
		else {
			$this -> _inputFileNames = array($inputFileName);
		}
		
		//open the input Excel file
		$fileFormat = $this->_fileFormat;
		$log -> info("!!Loading " . $this -> _config->getSetting('fileFormat') . " file '$inputFile'");
		$this->_inputFile = ADD_Util::loadClass('LRB_DataFile_' . $config->getSetting('fileFormat'), $inputFile);

		$this->_inputFile -> open($this -> _config);
		if (isset($this -> _config['limit']))
			$df -> setLimit($this -> _config['limit']);

		$log -> info("Processing rows");
		$this -> _idField = $config->getSetting('idField');
		$this -> _fnameField = $config->getSetting('firstNameField');
		$this -> _lnameField = $config->getSetting('lastNameField');
		$this -> _dobField = $config->getSetting('dobField');
	}
	
	function nextPatient() {
		$sourcePatient = $this->_inputFile -> nextRow();
		list($patientId, $fname, $lname, $dob) = ADD_Util::getArrayValue(array($this -> _idField, $this -> _fnameField, $this -> _lnameField, $this -> _dobField), $sourcePatient);
		if (isset($this->_config['skipBlanks']) && strlen($patientId)==0 && strlen($fname)==0 && strlen($lname)==0) {
			$log->info("Skipping blank row");
			$this->_skipRows++;
			return null;
		}
		
		//get the input ID and patient info	
		if (is_numeric($dob)) {
			$unixDate = ($dob - 25569) * 86400;
			$dob = gmdate("d/m/Y", $unixDate);
		}
		$fname = trim(strtoupper($fname));
		$lname = trim(strtoupper($lname));

		//clean the patient ID of spaces and other non-alphanumeric chars
		$patientId = preg_replace("/[-|\/].+$/", "", $patientId);
		$fname = preg_replace("/\(.+\)/", "", $fname);
		$lname = preg_replace("/\(.+\)/", "", $lname);
		
		//format DOB as d/m/y
		$temp = explode("/", $dob);

		$dmyDob = null;
		if (sizeof($temp) == 3) {
			if (strlen($temp[2]) == 2) {
				$temp[2] = "19" . $temp[2];
			}
			$dmyDob = sprintf("%02s/%02s/%s", $temp[0], $temp[1], $temp[2]);
		}
		
		if ($this->_inputFile -> getRowCount() % 100 == 0) {
				$log -> info("{$this->_inputFile->getRowCount()} rows processed");
		}
		return array("id"=>$patientId,"first_name"=>$fname,"last_name"=>$lname,"dob"=>$dmyDob, "raw"=>true);
	}
	function getHeadings() {
		return $this->_inputFile->getHeadings();
	}
	function destroy() {
		$this->_inputFile->close();
	}
}