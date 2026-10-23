<?php

use Symfony\Component\EventDispatcher\Event;
class ADD_EpisodeSectionEvent extends Event {
    private $episodeId;
    private $section;
    private $patientId;
    
    function setEpisodeId($episodeId) {
        $this->episodeId = $episodeId;
    }
    function getEpisodeId() {
        return $this->episodeId;
    }
    function setSection($section) {
        $this->section = $section;
    }
    function getSection() {
        return $this->section;
    }
    function getPatientId() {
        return $this->patientId;
    }
    function setPatientId($patientId) {
        $this->patientId = $patientId;
    }
}