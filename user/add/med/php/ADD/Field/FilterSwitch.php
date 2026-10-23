<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_FilterSwitch extends ADD_Field_Checkbox {
	function isStored() {
		return false;
	}
	function afterAddMarkup($page) {
		parent::afterAddMarkup($page);
		$this->addJsValidation("SwitchFilter",$page);
	}
}