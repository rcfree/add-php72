<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Content_Rtf implements ADD_ContentInterface {
	protected $_rtf;
	protected $_fileName;
	
	function init() {}
	function setRtf($rtf) {
		$this->_rtf = $rtf;
	}
		
	function toFile($reportFileName) {
		$reportDir = dirname($reportFileName);
			
		if (!is_writable($reportDir)) throw new ADD_ReportException("You cannot write to directory $reportDir");
            
        $this->_rtf->saveFile($reportFileName);
		$this->_fileName = $reportFileName;
	}
	function output() {
		if(!$this->_fileName) throw new ADD_ReportException("RTF file not written");
		$content = file_get_contents($this->_fileName);
		return $content;
	}
	function mimeType() {
		return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
	}
	
	function __toString() {
		try {
			return $this->output();
		}
		catch(Exception $ex) {
			return "<div class='alert alert-error'>Unexpected error:".ADD::web()->errorMessage($ex)."</div>";
		}
	}
	function clearBuffer() {
		$this->_buffer=null;
	}
	function headers() {
		return array(
			'Content-Disposition: attachment; filename="'.basename($this->_fileName).'"'
		);
	}
}