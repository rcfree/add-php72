<?php
/**
 * This class manages forms. It provides methods to load tab-delimited form definition files and convert them into Drupal forms
 * To set-up at file name is provided to the constructor. To generate the Drupal form structure, the
 * generateForm() method is used.
 * @author Rob Free
 * @version 0.1
 * @package ADD_Form
 */



class ADD_Component_Form_File extends ADD_Component_Form {
	protected $_formDir = "form";
	protected $_fileName = null;
	private $_dataFile = null;
	private $_noFinalise = false;
	
	function doNotFinalise() {
		return $this->_noFinalise;
	}
	function setNotFinalise($noFinalise) {
		$this->_noFinalise = $noFinalise;
	}
	function getFileName() {
		return $this->_fileName;
	}
	function setFileName($fileName) {
		$this->_fileName = $fileName;
	}
	
	function setup() {
            if ($this->isSetup()) return;
			$page = $this->_page;

            $add = ADD::sys();
            $modPath = $page->getFilePath();
            if (empty($modPath)) throw new ADD_FormException("Form file path is not set in module template");

            $fileName = $this->getFileName();
            if (!$fileName) $fileName = $page->getSetting('form');
            if (!$fileName) {
              return;
            }
			$fileBase = pathinfo($fileName);
			$formPath = $modPath."/".$this->_formDir."/".$fileName. (isset($fileBase['extension']) ? "" : ".".$page->getSetting('form_ext',"xlsx"));
            $formSetup = new ADD_Form_FileSetup($this->_fieldContent);
			$formSetup->loadAndInit($formPath, "RowData",$page);
            $this->setSetup(true);
	}
	
	function setDataFile($dataFile) {
		$this->_dataFile = $dataFile;
	}
	
	function getDataFile() {
		return $this->_dataFile;
	}
}
