<?php
class ADD_Content_CSV implements ADD_ContentInterface {
    private $_data;
	function setData($data) {
		$this->_data = $data;
	}
	function output() {
		return $this->_data;
	}
        
	function __toString() {
		return $this->output();
	}

	function mimeType() {
		return "application/csv";
	}
	function headers() {
		return array(
			'Content-Disposition: attachment; filename="export.csv"'
		);
	}
	function init() {
	}
}