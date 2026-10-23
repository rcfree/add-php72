<?php
class ADD_Component_CreateEpisodeSection extends ADD_Component {
    public function setup() {
        $events = $this->getPage()->getEvents();
        $events->addListener("episodeSection.create",array($this,"onCreate"));
    }
    public function onCreate($event) {
        $encType = $this->getPage()->getSetting('encounter');
        $pid = $event->getPatientId();
        $cer = ADD::sys()->getEncounterManager()->newSection($pid,$encType);
        $cer->setEncData("episode_id",$event->getEpisodeId());
        ADD::log()->info("Created new section");
        $cer->store();
        $event->setSection($cer);
    }
}