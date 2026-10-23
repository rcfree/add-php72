<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Client_Filter {
	private $filterGroup;
	function __construct($filterParam) {
		list($filterField,$filterOp,$filterValue) = explode("|",$filterParam);
		$filterValues = explode(";",$filterValue);
		$this->field = $filterField;
		$this->op = $filterOp;
		$this->values = $filterValues;
	}
	function getField() {
		return $this->field;
	}
	function getOp() {
		return $this->op;
	}
	function getValues() {
		return $this->values;
	}
}
	
				