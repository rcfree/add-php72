<?php

class ADD_Component_Encounter_SingleAction extends ADD_Component_Encounter_Single {
    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
		$em = ADD::sys()->getEncounterManager();
        $parent = ADD::sys()->getEncounterManager()->recordById(ADD::sys()->getParam('eid'));
		$encType = 'res-action-'.ADD::sys()->getParam('a');
		return $em->child($parent,$encType);
    }
   
}
?>