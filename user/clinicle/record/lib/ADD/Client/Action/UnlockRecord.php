<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



class ADD_Client_Action_UnlockRecord implements ADD_Client_Action_GridRowActionInterface {
	public function afterOutputMarkup(\ADD_Content_Html $html) {
		
	}

	public function getColumnHeading() {
	
	}

	public function isAvailable() {
		return true;
	}
  
	public function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addTextElement("a", "<i class='icon-lock'></i>&nbsp;Unlock record..", array("ng-show"=>"row.unlockable","class"=>"btn btn-mini","href" => "javascript:;", "ng-click"=>\ClinicLE\Client\Util\AngularJS::createCustomCall("unlockRecord",array("row.id"))));
		return $html;
	}

}
