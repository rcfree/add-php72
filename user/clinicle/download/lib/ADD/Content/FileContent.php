<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Content_FileContent extends ADD_Content_FileView implements ADD_ContentInterface {
	private $download=false;
	
	public function headers() {
		if($this->download) {
			return parent::headers();
		}
		return array(
			'Content-Disposition: inline'
		);
	}
	public function setDownload($download) {
		$this->download = $download;
	}
	public function isDownload() {
		return $this->download;
	}
}