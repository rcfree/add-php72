<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Codebreaker\Logic;
use ClinicLE\Service\Remote\Access;
use ClinicLE\Common\Util;
use ClinicLE\Common\Exception\NullValueException;
use Codebreaker\Logic\Exception\DataRetrieveException;
use ADD;

class SpellCollate {
	const DETAILS_PIM="wspell:sdetail";
	const ITEMS_PIM="wspell:comorbitem";
	const DIAGNOSES_PIM="wspell:diagnoses";
	
	const SPELL_TYPE="wspell:wspell";
	
	private $access;
	private $targetEncType;
	
	/**
	 * 
	 * @param Access $access
	 */
	public function __construct(Access $access, $targetEncType="blness-comorbitem") {
		$this->access = $access;
		$this->targetEncType = $targetEncType;
	}
	
	/**
	 * 
	 * @param type $patientIdentifier
	 */
	public function collateSpells($patientIdentifier) {
		
		$patient = $this->retrievePatient($patientIdentifier);
		if(!$patient) {
			return array();
		}
		$pid = Util::val("id",$patient);
		
		if(Util::isEmpty($pid)===true) {
			throw new NullValueException("ID in Codebreaker patient is missing");
		}
		$spells = $this->retrieveSpells($pid);
		if(count($spells)==0) {
			return array();
		}
		
		if(!is_array($spells)) {
			return array();
		}
		$diagnoses = $this->retrieveDiagnoses($pid);
		
		return $this->doCollation($pid,$spells,$diagnoses);
		
	}
	private function determineVerifiedSpells($diagnoses) {
		foreach($diagnoses as $diagnosis) {
			$spellId = Util::val("parent_encounter_id",$diagnosis);
			if(Util::isEmpty($spellId)===true) {
				throw new NullValueException("ID in Codebreaker spell diagnosis is missing");
			}
			$verifiedSpells[$diagnosis['parent_encounter_id']]=$diagnosis['is_verified']==1;
		}
		return $verifiedSpells;
	}
	
	private function doCollation($pid, $spells, $diagnoses) {
		$collatedSpells = array();
		
		$verifiedSpells = $this->determineVerifiedSpells($diagnoses);
				
		foreach($spells as $spell) {
			$spellId = Util::val("parent_encounter_id",$spell);
			if(Util::isEmpty($spellId)===true) {
				throw new NullValueException("ID in Codebreaker spell details is missing");
			}
			$items = $this->retrieveItems($pid, $spellId);
			$primary = $this->determinePrimary($items);
			$collatedSpells[]=array("value"=>$spellId,"label"=>$this->generateSpellLabel($spell,$verifiedSpells,$primary));
		}
		return $collatedSpells;
	}
	
	private function generateSpellLabel($spell,$verifiedSpells,$primary) {
		$spellLabel = $spell['date_of_admission']['value']." (".$spell['ward']['value'].") - ".$primary;
		if(Util::val($spell['parent_encounter_id'],$verifiedSpells)) {
			$spellLabel = "[$spellLabel]";
		}
		return $spellLabel;
	}
	
	private function determinePrimary($diagnoses) {
		if(!$diagnoses) {
			return "No primary diagnosis";
		}
		foreach($diagnoses as $diagnosis) {
			if($diagnosis['primary_diagnosis']['value']==1) {
				return $diagnosis['icd10_code']['label'];
			}
		}
		return "No primary diagnosis";
	}
	private function retrieveItems($pid, $spellId) {
		try {
			return $this->access->getEncountersUsingPim($pid, self::ITEMS_PIM,array("parentid"=>$spellId,"confirmparenttype"=>self::SPELL_TYPE));
		}
		catch(\ClinicLE\Service\Remote\Exception\RemoteDataException $ex) {
			throw new DataRetrieveException("Sorry unable to retrieve items from Codebreaker");
		}
	}
	private function retrievePatient($patientIdentifier) {
		try {
			$patientData = $this->access->getPatientWithIdentifier($patientIdentifier);
			return $patientData['data'];
		}
		catch(\ClinicLE\Service\Remote\Exception\RemoteDataException $ex) {
			throw new DataRetrieveException("Sorry unable to retrieve patient from Codebreaker. ".$ex->getMessage());
		}
	}
	
	private function retrieveSpells($pid) {
		try {
			return $this->access->getEncountersUsingPim($pid, self::DETAILS_PIM);
		}
		catch(\ClinicLE\Service\Remote\Exception\RemoteDataException $ex) {
			throw new DataRetrieveException("Sorry unable to retrieve ward spells from Codebreaker. ".$ex->getMessage());
		}
	}
	
	public function retrieveSpell($pid, $spellId) {
		$spells = $this->retrieveSpells($pid);
		foreach($spells as $spell) {
			if($spell['parent_encounter_id']!=$spellId) continue;
			return $spell;
		}
		return null;
	}
	
	private function retrieveDiagnoses($pid) {
		try {
			return $this->access->getEncountersUsingPim($pid, self::DIAGNOSES_PIM);
		}
		catch(\ClinicLE\Service\Remote\Exception\RemoteDataException $ex) {
			throw new DataRetrieveException("Sorry unable to retrieve diagnoses from Codebreaker");
		}
	}
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $sourceParentId
	 * @param type $targetParentId
	 * @param type $pim
	 */
	public function updateItems($sourcePatientIdent, $sourceParentId, $targetParentId, $pim) {
		if(!$sourceParentId) throw new DataRetrieveException("No codebreaker spell ID was provided");
		try {
			$patient = $this->retrievePatient($sourcePatientIdent);
			if(!$patient) {
				return array();
			}
			$patientId = Util::val("id",$patient);
			$items = $this->access->getEncountersUsingPim($patientId, $pim, array("parentid"=>$sourceParentId,"confirmparenttype"=>self::SPELL_TYPE));
		}
		catch(\ClinicLE\Service\Remote\Exception\RemoteDataException $ex) {
			throw new DataRetrieveException("Sorry unable to copy items from Codebreaker".$ex->getMessage());
		}
		if(!isset($items)) {
			ADD::web()->addWarning("No items in ward spell $sourceParentId");
			return;
		}
		$encounterManager = ADD::sys()->getEncounterManager();
		$parent = $encounterManager->sectionById($targetParentId);
		$parent->retrieve();
		$comorbItems = $encounterManager->children($parent,$this->targetEncType);
		foreach($comorbItems as $comorbItem) {
			$comorbItem->delete();
		}
		$fieldNames = array("icd10_code","uhl_qualifier","primary_diagnosis");
		
		$priDiag = null;
		foreach($items as $item) {
			if($item['primary_diagnosis']['value']==1) {
				$priDiag = $item;
			}
			$er = $encounterManager->newChild($parent,$this->targetEncType);
			$record = array();
			foreach($fieldNames as $fieldName) {
				$record[$fieldName]=$item[$fieldName]['value'];
			}
			
			$er->setObsValues($record);
			
			$er->store();
		}
		$primaryIcd = $priDiag['icd10_code']['value'];
		$asthmaCodes = array("J45");
		$copdCodes = array("J44.0");
		if(in_array($primaryIcd,$asthmaCodes)) {
			 $priDiag = 1;
		}
		if(in_array($primaryIcd,$copdCodes)) {
			$priDiag = 2;
		}
		return array("detail"=>$this->retrieveSpell($patientId,$sourceParentId),"primary"=>$priDiag);
	}

}
