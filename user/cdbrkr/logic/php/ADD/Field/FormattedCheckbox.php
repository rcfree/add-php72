<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ADD_Field_FormattedCheckbox extends ADD_Field_Checkbox {
	public function getDisplayText($form,$id) {
		if ($this->getValue()=='1') {
			return $this->getSetting("on_label","*");
		}
		else {
			return $this->getSetting("off_label"," ");
		}
	}
}
