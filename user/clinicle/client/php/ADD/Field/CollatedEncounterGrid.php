<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Field_CollatedEncounterGrid extends ADD_Field_EncounterGrid {
	
	function getDataUrl() {
		return "/api/collated-encounters";
	}
	function _confirmFieldsExist($colFieldNames,$form) {
		$colFields = array();
		
		foreach ($colFieldNames as $colFieldName) {
			if($colFieldName=="domain" || $colFieldName=="badge") {
				$colFields[]=new ADD_Field_Text(array("Field"=>$colFieldName));
				continue;
			}
			$field = $form->getField($colFieldName);
			if (!$field)
				throw new ADD_FieldException("Field name '$colFieldName' specified as column in EncounterTable not found in form definition");
			$colFields[] = $field;
		}
		return $colFields;
	}
}
