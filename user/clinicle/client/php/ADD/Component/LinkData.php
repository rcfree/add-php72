<?php
use Clinicle\Search\LinkedRecordQuery;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Component_LinkData extends ADD_Component implements ADD_Component_AjaxInterface {
	function ajaxFunction($values, ADD_Page $callPage) {
		$add = ADD::sys();
		$pid = $add -> getPatientId();
		$dataFieldName = ADD_Util::val("field",$values);
		$callForm = $callPage->getComponent('Form');
		$callForm->setup();
		$dataField = $callForm->getField($dataFieldName);
		$dataValues = explode(";",$dataField->getSetting("populate_values",""));
		if($dataValues=array("")) {
			return;
		}
		$ajaxInputs = ADD::sys()->getPageFromPim("ajax:ajax")->createAjaxInputs($callPage, "LinkData");
		$linkValue = ADD_Util::val($dataFieldName,$values);
		$dateNull = ($linkValue == null);
		$dataDate = null;
		$values = array();
		if($dateNull===false) {
			$dataDate = substr($linkValue,0,10);
		}
		
		foreach($dataValues as $dataValue) {
			$dataIndex = ADD_Util::labelToName($dataValue);
			if($dateNull === true) {
				$values[$dataIndex."_value"] = null;
				$values[$dataIndex."_date"] = null;
			}
			else {
				$query = new LinkedRecordQuery($add->getPatientId(),"path-tests-haemresult");
			
				$query->filterBy("coll_date_time",$linkValue);
				$query->filterBy("test_code",$dataValue);
				
				$values[$dataIndex."_value"] = $query->fetchSingle("result");
				$values[$dataIndex."_date"] = $dataDate;
			}
		}
			
		return $values;
	}
	function ajaxInputs(ADD_Page $callPage) {
		return array("SELF");
	}
}