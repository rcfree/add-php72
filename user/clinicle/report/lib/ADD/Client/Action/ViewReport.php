<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Client_Action_ViewReport implements ADD_Client_Action_GridRowActionInterface {
	function isAvailable() {
		return true;
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		//$reportUri = ADD_Util::uri("report/view",array("file"=>"row.report_file"));
		
		$html= "<a ng-cloak ng-show='row.viewable' target='_blank' ng-href='/report/view?file={{row.report_file}}'>View previous..</a>";
		$html.="<span ng-cloak ng-hide='row.viewable' class='label label-warning'>No permission to view report</span>";
		
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}