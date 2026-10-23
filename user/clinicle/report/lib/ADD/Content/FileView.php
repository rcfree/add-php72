<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Content_FileView implements ADD_ContentInterface {

	public function headers() {
		return array(
			'Content-Disposition: attachment; filename="'.basename($this->_fileName).'"'
		);
	}

	public function init() {
		
	}

	public function mimeType() {
		$pathInfo = pathinfo($this->_fileName);
		if($pathInfo['extension']=="pdf") {
			return "application/pdf";
		}
		if($pathInfo['extension']=="docx") {
			return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		}
        if($pathInfo['extension']=="csv") {
            return "text/csv";
        }
	}

	public function output() {
		return file_get_contents($this->_fileName);
	}
	
	public function setFileName($fileName) {
		$this->_fileName = $fileName;
	}
}