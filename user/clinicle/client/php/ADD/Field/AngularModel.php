<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_AngularModel extends ADD_Field_Container {
	function startFieldHtml($page) {
		$attrs = array("id"=>$this->getName(),"ng-controller"=>$this->assertSetting('controller'),"cle-pid"=>ADD::sys()->getPatientId(),"cle-refresh"=>"comorb_results_grid","cle-parentid"=>ADD::sys()->getParam('eid'));
		if($this->getSetting('pim')) {
			$attrs["cle-pim"]=$this->getSetting('pim');
		}
		$page->html()->startElement("div",$attrs);
	}
	function endFieldHtml($page) {
		$page->html()->endElement("div");
	}
}
