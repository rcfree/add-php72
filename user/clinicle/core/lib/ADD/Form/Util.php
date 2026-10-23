<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Util
 *
 * @author rcf8
 */
class ADD_Form_Util {
	protected static $_currFieldNo=0;
	 static function extractLevelAndFieldName($field) {
		$level = 1;
		if ($field->getFieldData('Level')) {
			$fieldName = self::extractFieldName($field->getFieldData("Field"),$field->getFieldData("Title"));
			return array($field->getFieldData('Level'),$fieldName);
		}
		
		while($level) {
			$item = $field->getFieldData("L$level");
			if($item) {
				$curr_level = $item;
				if (!empty($curr_level)) break;
			}
			$level++;
			if ($level>20) return array(1,$field->getFieldData('Field'));
		}
		return array($level,$field->getFieldData("L$level"));
	}
	static function extractFieldName($name,$title) {
	 	if (!$name && !$title) {
	 		self::$_currFieldNo++;
	 		return "field_".self::$_currFieldNo;
		}
		if (!$name) {
			return strtolower(preg_replace("/[^a-zA-Z0-9\_\.\#]/","_",strip_tags($title)));
		}
		if ($name=="*") {
			self::$_currFieldNo++;
	 		return "field_".self::$_currFieldNo;
		}	
		return strtolower(preg_replace("/[^a-zA-Z0-9\_\.\#]/","_",$name));
	 }
	 
}
