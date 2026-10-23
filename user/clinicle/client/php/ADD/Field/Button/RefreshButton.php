<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_Button_RefreshButton extends ADD_Field_Button {
	protected function getOnClick($page) {
		$refreshTarget = $this->getSetting('refresh_target');
		return "CLE.Client.refresh('".$refreshTarget."')";
	}
	protected function getLinkAttrs($page) {
		return array(
			"class"=>"btn btn-success ignoredirty",
			"type"=>"button"
		);
	}
	protected function getLinkTitle($page) {
		return "<i class='icon-refresh'></i>&nbsp;".$this->getTitle();
	}
}
?>