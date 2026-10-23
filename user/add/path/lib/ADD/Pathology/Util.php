<?php

class ADD_Pathology_Util {
	static function remapArrays($mapping,$data) {
		$remap = array();
		foreach($mapping as $srcName => $tgtName) {
			if(array_key_exists($srcName, $data)) {
				$remap[$tgtName]=$data[$srcName];
			}
		}
		
		return $remap;
	}
	static function limitArrays($limitTo,$data) {
		$limit = array();	
		foreach($limitTo as $key) {
			if(!array_key_exists($key,$data)) continue;
			$limit[$key]=$data[$key];
		}
		return $limit;
	}
	static function mssqldate_to_dmy($msdate) {
	    if (empty($msdate)) return "";
	    $date = date_parse($msdate);
        return sprintf("%02d",$date['day'])."/".sprintf("%02d",$date['month'])."/".sprintf("%04d",$date['year']);              
	}
}