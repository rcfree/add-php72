<?php
class ADD_Component_DrugDuplicate extends ADD_Component {
    public function setup() {
        $events = $this->getPage()->getEvents();
        $events->addListener("episodeSection.create",array($this,"onEpisodeSectionCreate"));
    }
    public function onEpisodeSectionCreate($event) {
        $episode = $event->getEpisode();    
        $section = $event->getSection();

        $em = ADD::sys()->getEncounterManager();
        $med = $em->recordById($encounter['id']);
        $med->retrieve();
        
        $obsValues = array();
        
        $section = $med->getParent();
        $section->retrieve();
        $drugs = $em->children($section,"med-general-drug");
        
        $pid = ADD::sys()->getPatientId();
        $encType = $this->getPage()->getSetting('encounter');
        $cer = ADD::sys()->getEncounterManager()->newSection($pid,$encType);
        $cer->setEncData("episode_id",$epid);
        $cer->store();
        
        $ddiagnosis = $em->newChild($cer,$this->getSetting("target-encounter-type"));
        
        $ddiagnosis->store($obsValues);
        
        foreach($comorbs as $comorb) {
            $comorb->retrieve();
            $newComorb = $em->newChild($cer,"blness-comorbitem");
            $newComorb->store($comorb->getObsValues());
        }
    }
}