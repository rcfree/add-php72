<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EpisodeQuery
 *
 * @author rob
 */
namespace ClinicLE\Episode\Data;
use ClinicLE\Common\Connection\AbstractDatabaseService;

class EpisodeGroup extends AbstractDatabaseService {
	function getEpisodeWithSectionId($sectionId) {
		return $this->getConnection()->fetchFirst("select e.* from episode e left join section s on e.id = s.episode_id where s.id=?",array($sectionId));
	}
	function addEpisodeType($episodeType) {
		$this->getConnection()->insert("episode_type",array("name"=>$episodeType));
	}
	function getEpisodeType($episodeType) {
		return $this->getConnection()->fetchFirst("select * from episode_type where name = ?",array($episodeType));
	}
	function doesEpisodeHaveSection($sectionType,$episodeId) {
		$count = $this->getConnection()->fetchSingleValue("select count(*) from section s left join section_type st on s.section_type_id = st.id where st.name=? and s.episode_id=?",array($sectionType,$episodeId));
		return $count > 0 ? true : false;
	}
	
	function getSectionInEpisode($sectionType, $episodeId) {
		$sectionData = $this->getConnection()->fetchFirst("select s.* from section s 
        left join section_type st on s.section_type_id = st.id 
        where s.episode_id = ? and st.name=?",array($episodeId, $sectionType));
		$section = new \ClinicLE\Data\Record\SectionRecord();
		if($sectionData) {
			$section->setEncounter($sectionData);
		}
		return $section;
	}
    function hasSectionInEpisode($sectionType, $episodeId) {
        return $this->getConnection()->fetchSingleValue("select count(*) from section s 
        left join section_type st on s.section_type_id = st.id 
        where s.episode_id = ? and st.name=?",array($episodeId, $sectionType)) > 0;
    }
	function getLinkedSectionEncounterInEpisode($encounterType, $sectionId) {
		$encounterData = $this->getConnection()->fetchFirst("select lnk_e.* from section s "
				. "left join episode ep on s.episode_id = ep.id "
				. "left join section lnk_s on lnk_s.episode_id = ep.id "
				. "left join section_type lnk_st on lnk_st.id = lnk_s.section_type_id "
				. "left join encounter lnk_e on lnk_s.id = lnk_e.parent_encounter_id "
				. "left join encounter_form lnk_ef on lnk_e.encounter_form_id = lnk_ef.id "
				. "where s.id=? and lnk_ef.type=?",array($sectionId, $encounterType));
		$encounter = new \ClinicLE\Data\Record\EncounterRecord();
		$encounter->setType($encounterType);
		if($encounterData) {
			$encounter->setEncounter($encounterData);
		}
		return $encounter;
	}
	function getSectionsInEpisode($sectionType, $episodeId) {
		$sectionRows = $this->getConnection()->fetchAll("select s.* from section s 
        left join section_type st on s.section_type_id = st.id 
        where s.episode_id = ? and st.name=?",array($episodeId, $sectionType));
		
		$sections = array();
		foreach($sectionRows as $sectionData) {
			$section = new \ClinicLE\Data\Record\SectionRecord();
			$section->setEncounter($sectionData);
			$sections[]=$section;
		}
		return $sections;
	}
	function addEpisode($episodeTypeId, $patientId) {
		return $this->getConnection()->insert("episode",array("patient_id"=>$patientId,"episode_type_id"=>$episodeTypeId));
	}
	
	function hasNoEpisodes($episodeType,$patientId) {
		$params = array($episodeType, $patientId);
		$episodeCount = $this->getConnection()->fetchSingleValue("select count(*) from episode e left join episode_type et on e.episode_type_id=et.id where et.name=? and e.patient_id=? and e.is_deleted!=1",$params);
		if($episodeCount==0) {
			return true;
		}
		return false;
	}
}
