<?php


class ADD_Import_EncounterOutput {
	private $_inputFile = null;
	private $_outputDir = null;
	private $_plugins = null;
	private $_outputFile = null;
	private $_mappings = array();
	private $_encIdByPatient=array();
	private $_headings=array();
	private $_preHeadings = array("patient_id", "patient_hash", "id", "parent", "action", "page");
	function __construct($options) {
		if (isset($options['inputFile'])) $this -> _inputFile = new LRB_DataFile_Delimited($options['inputFile']);
		$this->_outputDir = $options['outputDir'];
		$this->_outputPrefix = $options['outputPrefix'];
		
		$mappings = array();
		if (isset($options['mapping'])) {
			foreach($options['mapping'] as $name => $mapping) {
				$mappings[$name]=new ADD_Import_Mapping($mapping);
			}
			$this->_mappings=$mappings;
		}
		if (isset($options['pluginsDir'])) {
			set_include_path(get_include_path().":".$options['pluginsDir']);
		}
		if (isset($options['plugins'])) {
			$this->_plugins = new ADD_Tool_ClassSet($options['plugins'],"ADD_Import_EncounterOutput_Plugin");
			$this->_plugins->callMethod('init',$this);
		}
		
	}
	function setInputFile($inputFile) {
		$this->_inputFile = $inputFile;
	}
	function getInputFile() {
		return $this->_inputFile;
	}
	function setHeadings($headings) {
		$this->_headings=$headings;
	}
	function getPatientEncId($row) {
		$patientId=$row['patient_id'];
		return ADD_Util::getArrayValue($patientId,$this->_encIdByPatient);
	}
	function addRow($row,$id, $parentId, $action, $page, $fields) {
		$patientId = $row['patient_id'];
		$patientMd5 = isset($row['patient_hash']) ? $row['patient_hash'] : null;
		$output = array($patientId, $patientMd5,$id,$parentId,$action,$page);

		foreach($this->_headings as $heading) {
			$output[]=ADD_Util::getArrayValue($heading,$fields);
		}
		
		fputcsv($this->_outputFile, $output);
	}
	function changeFields($input,&$output,$mappings) {
		foreach($mappings as $srcMap => $tgtMap) {
			$output[$tgtMap]=ADD_Util::getArrayValue($srcMap,$input);	
		}
	}
	function run() {
		if ($this->_plugins) $this->_plugins->callMethod('preRun',$this);
		
		$this->_inputFile->open();
		
		$bcount = ceil($this->_inputFile->lineCount()/$this->_inputFile->getBatchSize());
		global $log;
		$log->info("Batch count:$bcount");
		if (!is_dir($this->_outputDir)) {
			mkdir($this->_outputDir, 0755, true);
		}
		
		$currentBatch=1;
		while ($dbatch = $this->_inputFile -> nextBatch()) {
			$ofName = $this->_outputDir. "/" . $this->_outputPrefix."_".$currentBatch . ".csv";
			$this->_outputFile = fopen($ofName, "w");
			$log->info("Output filename:$ofName");
			fputcsv($this->_outputFile, array_merge($this->_preHeadings,$this->_headings));
			$iCounter=1;
			foreach ($dbatch as $ditem) {
				$this->_encIdByPatient[$ditem['patient_id']] = $iCounter;
				
				$opts = array($ditem,$this);
				if ($this->_plugins) $this->_plugins->callMethod('processRow',$opts);
				$iCounter++;
			}
			
			$log -> info("Batch " . $currentBatch . "/$bcount processed");
			$currentBatch++;
			fclose($this->_outputFile);
			
		}
		if ($this->_plugins) $this->_plugins->callMethod('postRun',$this);
		
	}

}
?>