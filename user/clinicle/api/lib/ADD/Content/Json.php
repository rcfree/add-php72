<?php
class ADD_Content_Json {
    private $_jsonData = array();
	function output() {
            return json_encode($this->_jsonData);
        }
        
	function __toString() {
		return $this->output();
	}
        
        function setJsonData($jsonData) {
            $this->_jsonData = $jsonData;
        }
        function getJsonData() {
            return $this->_jsonData;
        }
        function mimeType() {
            return "application/json";
        }
		function headers() {
			return null;
		}
}