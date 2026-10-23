<?php
class ADD_Component_CopyEpisodeSectionSupport extends ADD_Component {
    private $event;
    public function setup() {
        $events = $this->getPage()->getEvents();
        $events->addListener("episodeSection.beforeCreate",array($this,"onBeforeCreate"));
        $events->addListener("episodeSection.afterCreate",array($this,"onAfterCreate"));
    }
    public function onBeforeCreate($event) {
        $events = $this->getPage()->getEvents();
        $this->event = new ADD_CopyEpisodeSectionEvent($event);
        $events->dispatch("copyEpisodeSection.beforeCopy",$this->event);
    }
    public function onAfterCreate($event) {
        $events = $this->getPage()->getEvents();
        
        $events->dispatch("copyEpisodeSection.copy",$this->event);
    }
}