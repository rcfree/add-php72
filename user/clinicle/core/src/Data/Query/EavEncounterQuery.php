<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Query;
use ClinicLE\Common\Exception\NullValueException;
use ADD;
use ClinicLE\Data\Record\EncounterRecord;

class EavEncounterQuery extends AbstractEncounterQuery {

    public function generateFrom() {
		$encForm = ADD::db('encounter')->getEncounterFormWithType($this->typeFilter);
		$this->filterByEncounterField("is_deleted","1","!=");
		$this->filterByEncounterField("encounter_form_id",$encForm['id']);
		return "from encounter e";
	}
	
	public function getFieldSubQuery($fieldName, $encType = "e") {
        $encAlias = $this->determineEncAlias($encType);
		return "(select concat(o.value, coalesce(o.note,'')) from observation o
					left join observation_field of on o.observation_field_id=of.id
					where o.encounter_id=$encAlias.id and of.name='$fieldName'
				)";
	}

	 public function filterBySectionId($parentId, $operator="=") {
         $encAlias = $this->determineEncAlias();
		$this->addFilter($encAlias.".".EncounterRecord::SECTION_FIELD,$operator,$parentId);
		return $this;
    }
	public function filterBySectionType($sectionType) {
        $encAlias = $this->determineEncAlias();
        $this->addJoinTable("section `s`",$encAlias.".".EncounterRecord::SECTION_FIELD." = s.id");
        $this->addJoinTable("section_type `st`","s.section_type_id = st.id");
        $this->addFilter("st.name","=",$sectionType);
    }
	public function filterByObservationField($field,$value,$operator="=", $encType="e") {
		$this->addFilter($this->getFieldSubQuery($field,$encType),$operator,$value);
		return $this;
    }

	function joinSection($sectionAlias,$sectionTypeId) {
		$this->addJoinTable("section `".$sectionAlias."`","`".$this->determineEncAlias()."`."
            .EncounterRecord::SECTION_FIELD." = `{$sectionAlias}`.id and `$sectionAlias`.section_type_id=$sectionTypeId");
	}
	function joinSectionEncounter($encounterType,$encounterFormId) {
        $encAlias = $this->determineEncAlias();
		$this->addJoinTable("encounter `".$encounterType."`",$encAlias.".".EncounterRecord::SECTION_FIELD." = `".$encounterType."`.".EncounterRecord::SECTION_FIELD. " and `$encounterType`.encounter_form_id=$encounterFormId");
	}
}