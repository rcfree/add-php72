<?php

use Symfony\Component\EventDispatcher\Event;
class ADD_EpisodeMenuSectionEvent extends ADD_EpisodeSectionEvent {
    private $episodeId;
    private $section;
    private $patientId;
    private $label;
	
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
	function setLabel($label) {
		$this->label = $label;
	}
	
	function getLabel() {
		return $this->label;
	}
}