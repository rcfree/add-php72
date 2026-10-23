<?php

class ADD_Component_Encounter_Report extends ADD_Component_Encounter {
	private $section=false;
	function initEncounterRecord($idValue = null) {
        $page = $this->_page;
		$encType = $page->getSetting('encounter');
		$em = ADD::sys()->getEncounterManager();
		if(ADD::sys()->getParam('type')=="section") {
			$this->setSection(true);
			$er = $em->sectionById($idValue);
		}
		else {
			$er = $em->record($encType,ADD::sys()->getPatientId());
			$er->setId($idValue);
		}
		return $er;
    }
	function setSection($section) {
		$this->section = $section;
	}
	function isSection() {
		return $this->section;
	}
}