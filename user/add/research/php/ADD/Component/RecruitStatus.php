<?php

class ADD_Component_RecruitStatus extends ADD_Component {
    function submit($values) {
		$add = ADD::sys();
		
		$detailPage = $add->getModule('research')->getPage('details');
		$detailRecord = $detailPage->getEncounterRecord();
		$detailRecord->retrieve();
		$detailRecord->setObsValue("current_status",$values['recruit_status']);
		$detailRecord->store();
	}		
} 