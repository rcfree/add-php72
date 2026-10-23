<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Page_Api_Matrix extends ADD_Page_Api_Encounters {
	function apiOutput() {
		$data = parent::apiOutput();	
		$grid = array();
		$xField = ADD::sys()->assertParam('xfield');
		$yField = ADD::sys()->assertParam('yfield');
		$valueField = ADD::sys()->assertParam('valuefield');
		$matrixData = new ADD_Client_Util_MatrixConvert($xField,$yField,$valueField);
		
		return array("data"=>$matrixData->process($data['data']));	
	}
	function _getValuePosInArray($key,&$array) {
		$pos = array_search($key,$array);
		if($pos!==false) {
			return $pos;
		}
		$array[]=$key;
		return count($array)-1;
	}
	function isOnlyLabelOutput() {
		return false;
	}
}