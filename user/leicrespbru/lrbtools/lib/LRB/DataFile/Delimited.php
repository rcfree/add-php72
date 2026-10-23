<?php
/**
 * This is the derived DataFile class which loads Excel files.
 * Each row is put into an associative array (based on the file header) and added to an array accessible through getDataItems().
 * @author Rob Free
 * @version 0.1
 * @package LRBTool_DataFile_ExcelBase
 */

class LRB_DataFile_Delimited extends LRB_DataFile_Base {
	
	private $fieldSep = ",";
	private $rowCount = 0;
	private $handle = null;
	private $batchSize = 5000;
	private $trimFields = false;
	private $limit = null;
	private $finalBatch = false;
	
	function getBatchSize() {
		return $this->batchSize;
	}
	function getRowCount() {
		return $this->rowCount;
	}
	function setFieldSep($fieldSep) {
		$this->fieldSep = $fieldSep;
	}
	
	function setBatchSize($batchSize) {
		$this->batchSize = $batchSize;
	}
	
	function setTrimFields($trimFields) { 
		$this->trimFields = $trimFields;
	}
	
	function setLimit($limit) {
		$this->limit = $limit;
	}
	
	function getLimit() {
		return $this->limit;
	}
	
	/**
	 * Load Excel data file as array of associative arrays into dataItems property of DataFile object
	 * @param string $fileName
	 */
	function load() {
		$this->open();
		$dataItems = array();
		#loop through each CSV separated line of file
		while ($item = $this->nextRow()) {
			$dataItems[] = $item;
		}
		$this->close();
		$this->setDataItems($dataItems);
	}
	
	function open() {
		$dataItems = array();
		$headings = $this->getHeadings();
		$this->setDataItems(null);
		
		#used to allow \n line endings (PHP doesn't support this by default!)
		ini_set("auto_detect_line_endings", true);
		
		#open CSV file
		if (($handle = @fopen($this->getFileName(), "r")) === FALSE) {
                    throw new Exception("Unable to open form file '{$this->getFileName()}'");
		}
                
		if ($this->hasHeaderRow() && $this->rowCount==0) {
                    $newHeadings = fgetcsv($handle, 0, $this->fieldSep);
                    if(!$headings) $this->setHeadings($newHeadings);
                    $this->rowCount++;
                }
		$this->handle = $handle;
	}
	
	function nextRow() {
		#loop through each CSV separated line of file
		$csv_data = fgetcsv($this->handle, 0, $this->fieldSep);
		if(!$csv_data) return null;
		if ($this->trimFields) $csv_data = array_map('trim',$csv_data);
		$item = array();
		#if headings map data as assoc array using $headings
		$c = 0;
		#var_export($this->headings);
        if ($this->getLimit() && $this->rowCount>$this->getLimit()) return null;
		        
		foreach ($this->getHeadings() as $heading) {
			$item[$heading]=isset($csv_data[$c]) ? $csv_data[$c] : null;
			$c++;
		}
		$this->rowCount++;
		return $item;
	}
	function lineCount() {
		$lines = system("cat {$this->getFileName()} | wc -l");
		return $lines;
	}
	
	function nextBatch() {
		#loop through each CSV separated line of file
		$batch = array();
		$bCount = 0;
		$bSize = $this->getBatchSize();
		if ($this->finalBatch) return false;
		while($row = $this->nextRow()) {
			$batch[]=$row;
			if ($bCount==$bSize) return $batch;
			if ($this->getLimit() && $bCount+1>=$this->getLimit()) {
				$this->finalBatch=true;
				return $batch;
			}
			$bCount++;
		}
		if (count($batch)==0) return null;
		return $batch;
	}
	
	function close() {
		fclose($this->handle);
	}
	
	function reset() {
		$this->rowCount=0;
		$this->open();
	}
	
}
