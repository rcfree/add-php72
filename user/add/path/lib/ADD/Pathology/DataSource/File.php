<?php
use ClinicLE\Common\Config\AbstractConfig;

class ADD_Pathology_DataSource_File extends ADD_Pathology_DataSource_Base {
	public function __construct(AbstractConfig $config) {
		$this->_dataFile = new LRB_DataFile_Delimited($config->getSetting('file'));
		$this->_dataFile->setHeaderRow(true);
		$this->_dataFile->setFieldSep("\t");
		$this->_dataFile->open();
	}
	public function nextTestItem() {
		$row = $this->_dataFile->nextRow();
		return $row;
	}
	function getTestItemTotal() {
		return $this->_dataFile->lineCount();
	}
}
