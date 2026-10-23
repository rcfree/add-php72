<?php

use Symfony\Component\EventDispatcher\Event;
class ADD_CopyEpisodeSectionEvent extends Event {
    private $sourceSection;
    
    function __construct($episode) {
        $this->episode = $episode;
    }
    function setEpisodeId($episodeId) {
        $this->episode->setEpisodeId($episodeId);
    }
    function getEpisodeId() {
        return $this->episode->getEpisodeId();
    }
    function setPatientId($patientId) {
        $this->episode->setPatientId($patientId);
    }
    function getPatientId() {
        return $this->episode->getPatientId();
    }
    function setSourceSection($sourceSection) {
        $this->sourceSection = $sourceSection;
    }
    function getSourceSection() {
        return $this->sourceSection;
    }
    function setTargetSection($targetSection) {
        $this->episode->setSection($targetSection);
    }
    function getTargetSection() {
        return $this->episode->getSection();
    }
    function stopPropagation() {
        $this->episode->stopPropagation();
    }
    function isPropagationStopped() {
        return $this->episode->isPropagationStopped();
    }
}