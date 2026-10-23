<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Page_ReportItems extends ADD_Page_Api {
	function createMenuPath() {
		return "report/items";
	}
	function apiOutput() {
		$adb = ADD::db('audit');
		$reportsPrinted = $adb->fetchAll("select * from audit where record_type=? and record_name like ? and record_id=? order by timestamp desc",array("report",ADD::sys()->assertParam('report')."%",ADD::sys()->getPatientId()));
		
		$rows = array();
		$eid = ADD::sys()->getParam('parentid');
		$moduleName = ADD::sys()->assertParam('module');
		$module = ADD::sys()->getModule($moduleName);
		
		foreach($reportsPrinted as $reportPrinted) {
			$actionInfo = ADD_Util::unserialize($reportPrinted['action_info']);
			
			if($eid && ADD_Util::val("encounter_id",$actionInfo)!=$eid) continue;
			
			$reportFile = $actionInfo['file'];
			$fileName = str_replace(ADD::sys()->config('report_path')."/","",$reportFile);
				
			$rowData=array("time_printed"=>ADD_Util::timeFromISOToDMYHM($reportPrinted['timestamp']),"printed_by"=>$reportPrinted['user_id'],"report_type"=>$reportPrinted['record_name']);

			$reportPage = $module->getPage($reportPrinted['record_name']);
			if(!$reportPage) {
				ADD::web()->addWarning("Report page '".$reportPrinted['record_name']."' was not found in module '".$module->getName()."'");
				continue;

			}
			if($reportPage->getPermission('view')) {
				$rowData['report_file']=$fileName;
				$rowData['viewable'] = true;
			}
			else {
				$rowData['viewable']=false;
			}
			$rows[]=$rowData;
		}
		return $rows;
		//return array($headings,$rows);
	}
}	