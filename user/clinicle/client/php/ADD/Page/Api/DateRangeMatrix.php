<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Page_Api_DateRangeMatrix extends ADD_Page_Api_Matrix {
	function appendFiltersToQuery($query) {
		parent::appendFiltersToQuery($query);
		$cdt = $this->_getFieldSubQuery("coll_date_time");
		//$where[] = "(STR_TO_DATE($cdt,'%d/%m/%Y') >= ? and STR_TO_DATE($cdt,'%d/%m/%Y') <= ?)";
		$includeFields = ADD::sys()->getParam('includefields');
		if(!$includeFields) {
			throw new Exception("No 'includefields' parameter provided:".json_encode(ADD::sys()->getParams()));
		}
		$visitDate = ADD::sys()->getParam($includeFields);
		$visitDt = ADD_Util::dateFromDmy($visitDate);
		if(!$visitDt) {
			ADD::web()->addWarning("Please enter a '".ADD_Util::nameToLabel($includeFields)."' to show appropriate data");
			//$params[]=null;
			//$params[]=null;
            $query->setDisabled(true);
			return;
		}
		$interval1m = new DateInterval("P1M");
		$interval6m = new DateInterval("P6M");
		$startDate = clone($visitDt);
		$startDate->sub($interval6m);
		$endDate = clone($visitDt);
		$endDate->add($interval1m);
		
		$query->addFilter("STR_TO_DATE($cdt,'%d/%m/%Y')",">=",$startDate->format("Y-m-d H:i:s"));
        $query->addFilter("STR_TO_DATE($cdt,'%d/%m/%Y')","<=",$endDate->format("Y-m-d H:i:s"));
	}


}