<?php

	class ADD_Tool_Import_Mapping {
		private $_mapping;
		private $_failures = null;
		private $_replaceRegEx;
		
		function __construct($fileNameOrMap, $replaceRegEx=null) {
			if (is_array($fileNameOrMap)) {
				$this->_mapping = $fileNameOrMap;
				$this->_replaceRegEx = $replaceRegEx;
				return;
			}
			$consMapping = array();
			$consMap = new LRBRUTool_DataFile_Excel($fileNameOrMap);
			$consMap->load();
			
			foreach($consMap->getDataItems() as $di) {
				$consMapping[$di['Source']]=$di['Target'];
			}
			$this->_mapping = $consMapping;
			$this->_replaceRegEx = $replaceRegEx;
		}
		
		function extract($value, $toLower=false) {
			if ($toLower) $value=strtolower($value);
			if ($this->_replaceRegEx) $value = preg_replace($this->_replaceRegEx,"",$value);
			
			if (strlen($value)>0) {
				if (isset($this->_mapping[$value])) {
					return $this->_mapping[$value];
				}
				else {
					$this->_failures[]=$value;
					return false;
				}
			}
		}
	
		function setMapping($mapping) {
			$this->_mapping = $mapping;
		}
		function getFailures() {
			return $this->_failures;
		}
	}
?>