<?php



class ADD_Component_Encounter_Domain_ProgramAutoEnrol extends ADD_Component_Encounter_Domain_AutoEnrol {

    
    public function menu($options) {
    		
        $add = ADD::sys();
        $page = $this->_page;
        if (!$add->getPatientId())
            return;
        
        if ($add->getCurrentPage()->hasComponent('RemovePatient')) {
        	return;
		}
        $patient = ADD::db('patient')->getPatient($add->getPatientId());
        if (!$patient || $patient['is_deleted']) return;
        
   
        $er = $page->getEncounterRecord();
        if (!$er->exists()) {
            return;
        }
       
    
        $this->_addDomainOptions($options);
    }

    function afterSubmit(ADD_Page $submitPage) {
    }

    function menuPath(&$menuPath) {
        
    }
	protected function menuDomainTitleExtra($options) {
		
	}
}
