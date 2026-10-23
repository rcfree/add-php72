<?php



class ADD_Component_SummaryUpdate extends ADD_Component {

    function afterSubmit(ADD_Page $submitPage) {
        $page = $this->_page;
        #only run if submitted page is same as current page
        if ($page->getName() != $submitPage->getName())
            return;

        if (!$page->isSetup())
            $page->setupComponents();

        $add = ADD::sys();

        $patientId = $add->getPatientId();
        $form = $page->getComponent('Form');
        $edb = ADD::db('encounter');
        $adb = ADD::db('audit');
        $ppage = $page->getParentPage();
        $er = $submitPage->getEncounterRecord();
		$er->retrieve();
		
        $sumObs = array();
        foreach ($form->getFields() as $field) {
        	$fname = $field->getName();
            if (!$field->getSetting('summary_obs'))
                continue;
			$targetSummaryObs = $field->getSetting('summary_obs');
			if($page->getSetting("SummaryUpdate.$targetSummaryObs")) {
				$targetSummaryObs = $page->getSetting("SummaryUpdate.$targetSummaryObs");
			}
            if ($field->getSetting('summary_value'))
                $sumObs[$targetSummaryObs] = ADD_Util::evaluate($field->getSetting('summary_value'));
            if ($field->getSetting('summary_type') == 'status') {
                $sumObs[$targetSummaryObs] = $er->getEncData('is_complete') ? "Complete" : "On-going";
            } else {
            	$val = $er->getObsValue($fname);
				if (ADD_Util::isEmpty($val)) $val="";
				$sumObs[$targetSummaryObs] = $val;
			}
        }
		$updEncType = $page->getSetting('summary_encounter');
		$encComp = $page->getComponent('Encounter');
		$per = $ppage->getEncounterRecord();
        $per->retrieve();
        $ser = ADD::sys()->getEncounterManager()->child($per,$updEncType);
        $ser->retrieve();
		
		#if no child encounter then create it
        if (!$ser->exists()) {
            $page->addMessage("Created summary record and added observations");   
        }
        #otherwise update the existing one
        else {
            $page->addMessage("Updated summary record with new observations");
        }
        $ser->store($sumObs);
    }

}

?>