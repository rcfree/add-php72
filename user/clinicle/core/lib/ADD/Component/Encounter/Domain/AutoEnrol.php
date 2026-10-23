<?php



class ADD_Component_Encounter_Domain_AutoEnrol extends ADD_Component_Encounter_Domain {

    public function menu($options) {
    	
        $add = ADD::sys();
        //$page = $this->_page;
        if (!$add->getPatientId())
            return;
        
        if ($add->getCurrentPage()->hasComponent('RemovePatient'))
            return;
        
            $patient = ADD::db('patient')->getPatient($add->getPatientId());
            if (!$patient || $patient['is_deleted']) return;
            
       /*
            $er = $page->getEncounterRecord();
            if (!$er->exists()) {
                $this->_enrolInDomain();
                return;
            }
           */
        
            $this->_addDomainOptions($options);
    }

    function isDomainRequired() {
        return false;
    }
    /*
    function generate() {
        $add = ADD::sys();
        //if (!$add->getParam('manual')) return;
        //$this->_enrolInDomain();

        $page = $this->_page;
        $addMessage = $page->getSetting('add_message', "Patient was added to " . $page->getSetting('title') . " successfully");
        $page->addMessage($addMessage);
        $page->redirectUsingSetting();
    }

    function afterSubmit(ADD_Page $submitPage) {
        $page = $this->_page;
		if(!ADD::sys()->getPatientId()) {
			return;
		}
        $submitMod = $submitPage->getModule();
        if ($submitPage->getName() != 'new' && $submitMod->getAlias() != 'patient') return;
        if (!$submitPage->isSetup())
            $submitPage->setupComponents();
        if ($page->getEncounterRecord()->exists()) return;
        
        $this->_enrolInDomain();
        $page->addMessage("Patient was added to the " . $page->getSetting('title') . " domain");
    }
*/
    function menuPath(&$menuPath) {
        $page = $this->_page;
        $menuPath = $page->getModule()->getName() . "/new";
    }

}
