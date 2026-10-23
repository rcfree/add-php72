<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ADD_Report_Function {
	static $values = array();
	static $dataPopulate;
	static function capitalise($fieldName) {
		return ucfirst(strtolower(ADD_Util::val($fieldName,self::$values)));
	}
	static function join($table, $fieldNames,$glue=", ",$ignoreValues=false) {
		$fieldNameList=explode(";",$fieldNames);
		$joinedList=array();
		foreach($fieldNameList as $fieldName) {
			$value = ADD_Util::val($table.".".$fieldName,self::$values);
			if($ignoreValues && ADD_Util::isEmpty($value)) continue;
			$joinedList[]=$value;
		}
		return implode($glue,$joinedList);
	}
	static function today($format="d/m/Y") {
		return date($format);
    }
	static function lookup($attrs) {
 		list($linkField,$lookupField)=explode(";",$attrs);

 		$linkValue = ADD_Util::val($linkField,self::$values);
 		if(!$linkValue) return "unknown";
 		$em = ADD::sys()->getEncounterManager();
 		$er = $em->recordById($linkValue);
 		if(!$er->exists()) {
 			return "unknown";
 		}
 		$er->retrieve();
 		return $er->getObsValue($lookupField);
	}
	static function diff($fieldNames) {
		list($field1,$field2)=explode(";",$fieldNames);
		
		$value1 = ADD_Util::val($field1,self::$values);
		$value2 = ADD_Util::val($field2,self::$values);
		if(ADD_Util::isEmpty($value1) || ADD_Util::isEmpty($value2)) return null;
		return $value1 - $value2;
	}
	
	static function fieldval($fieldName) {
		return ADD_Util::val($fieldName,self::$values['fieldval']);
	}
	static function hasFunction($name) {
		if(method_exists(__CLASS__,$name)) {
			return true;
		}
		return CLE::svc(ClinicLE\System\Service\CoreServices::FIELD_FUNCTIONS)->hasFunction($name);
	}
}

