<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Query_Util {
	static function initSystem($path,$profile) {
		set_include_path($path."/core/lib");
		;
		;
		$init = new ADD_Initialiser_Standard($path,$profile);
		$add = ADD::init($init);
		$add->initModules();
		;
		;
		;
		
		ADD::sys()->initModules();
		return $add;
	}
	static function initQuery($outputType="Simple") {
		;
		;
		;
		;
		
		$encounterLookup = new ADD_Query_EncounterLookup(ADD::sys()->getModules());
		
		$run = new ADD_Query_Run($encounterLookup);
		$output = ADD_Util::loadClass("ADD_Query_Output_$outputType");
		
		$result = new ADD_Query_Result($output,$run);
		return $result;
	}
	
static function makePlaceholders($params) {
        $count = is_array($params) ? count($params) : $params;
        $pholderList = array_fill(0, $count, "?");
        return implode(",", $pholderList);
    }

static function generateIdListInSql($idFields, $idLists, $additionalWhere=null, $additionalParams=array()) {
	$wheres = array();
	$params = array();
	if($idFields) {
		if(!is_array($idFields)) {
			$idLists = array($idFields=>$idLists);
			$idFields=array($idFields);
		}
		foreach($idFields as $idField) {
			$idList = isset($idLists[$idField]) ? $idLists[$idField] : null;
			
			if($idList===null) continue;
			
			if(count($idList)==0) {
				$wheres[]="$idField not like '%'";
				continue;
			}
			$placeholders = ADD_Query_Util::makePlaceholders($idList);
			$wheres[] = "$idField in ($placeholders)";
			$params=array_merge($params,$idList);
		}
	}
	
	if($additionalWhere) {
		$wheres[]=$additionalWhere;
		$params = array_merge($params,$additionalParams);
	}
	$whereClause="";
	if(count($wheres)>0) {
		$whereClause=" where ".implode(" and ",$wheres);
	}
	return array($whereClause,$params);
	
}
}
?>
