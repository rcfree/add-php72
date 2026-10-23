<?php

class ADD_Field_Option_Studies extends ADD_Field_Option_Standard {
	private $_allStudies = array();
	private $_studiesEnrolledIn=array();
    public function getStudiesEnrolledIn() {
		$encLabel = null;
		$add = ADD::sys();
		$em = ADD::sys()->getEncounterManager();
		
		$studies = $em->sectionsByPatientAndType($add->getPatientId(),"res-study");
		$reenrolStatuses = array(2,4,8);
		foreach($studies as $study) {
			$study->retrieve();
			$sdetails = $em->child($study,"res-study-details");
			$sdetails->retrieve();
			$studyId = $sdetails->getObsValue("study");
			$status = $sdetails->getObsValue("current_status");
			if(in_array($status,$reenrolStatuses)) {
				continue;
			}
			$this->_studiesEnrolledIn[]=$studyId;
		}
		return $this->_studiesEnrolledIn;
	}
	function initOptions($field,$page) {
		$spage = ADD::sys()->getPageInModule("research","study");
		$items = ADD::db('dictionary')->getDictionaryItems("studylist");
		
		if(!$field->getSetting('allStudies')) $this->getStudiesEnrolledIn();
        
		$options = array(""=>"-select study-");
		foreach($items as $item) {
			if(ADD_Util::val('is_disabled',$item)==1) continue;
			$this->_allStudies[$item['value']]=$item['title'];
			if(in_array($item['value'],$this->_studiesEnrolledIn)) continue;
			$options[$item['value']]=$item['title'];
		}
		asort($options);
        $this->_optionList=$this->_optionList+$options;
        $this->_conceptList=$field->getFieldData('Concept');
    }
	function findLabelWithValue($value)
    {
        return ADD_Util::val($value,$this->_allStudies);
    }
}
