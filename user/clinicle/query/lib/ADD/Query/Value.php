<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value extends ADD_Query_Element {
    protected $_fields = array();
    protected $_fieldMap = array();
	protected $_internal = false;
	
	public function addFieldsToResult(ADD_Query_Result $result) {
	}
	function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
	}
    public function __construct($attrs) { 
        parent::__construct($attrs);
        $this->_fields = $this->getAttr("fields");
		$this->_internal = $this->getAttr("internal",false);
    }
}