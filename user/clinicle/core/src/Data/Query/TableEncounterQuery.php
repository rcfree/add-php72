<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Query;

class TableEncounterQuery extends AbstractEncounterQuery {
	function getFieldSubQuery($fieldName, $encType = "e") {
		return "`".$encType."`.".$fieldName;
	}
	public function filterBySectionId($parentId, $operator="=") {
	$this->addFilter("e.section_id",$operator,$parentId);
	return $this;
    }
	function joinSection($sectionAlias,$sectionTypeId) {
		$this->joinTables["section `".$sectionAlias."`"]="e.section_id = {$sectionAlias}.id";
	}
	function joinSectionEncounter($encounterType,$encounterFormId) {
		$this->joinTables["`".$encounterType."`"]="e.section_id = `".$encounterType."`.section_id";
	}
}