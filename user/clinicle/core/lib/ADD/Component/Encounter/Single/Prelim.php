<?php

class ADD_Component_Encounter_Single_Prelim extends ADD_Component_Encounter_Single {
    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
        $add = ADD::sys();
        $er = $add->getEncounterManager()->record($page->getSetting('encounter'),$add->getPatientId());
        return $er;
    }
}
?>