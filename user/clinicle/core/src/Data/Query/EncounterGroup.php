<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Query;
use ClinicLE\Data\Record\EncounterRecord;
use ClinicLE\Data\Record\SectionRecord;
use ClinicLE\Data\Repository\AbstractEncounterRepository;

/**
 * Description of EncounterService
 *
 * @author rob
 */
class EncounterGroup {
    
	function __construct(AbstractEncounterRepository $repository) {
		$this->repository = $repository;
	}

	function createQuery() {
	    return $this->repository->createQuery();
    }
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @return \Clinicle\Database\Encounter
	 */
	public function getAllEncountersWithPatientAndType($patientId, $type, $otherFilters=array()) {
		if(!$patientId) throw new \InvalidArgumentException("No patient id supplied");
		$query = $this->repository->createQuery(); $query->filterByType($type);;
		$query->addEncounterFields("*");
		$query->addObservationFields("*");
		
		$query->filterByPatientId($patientId);
		return $this->retrieveRecordsUsingQuery($query);
	}
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @return \Clinicle\Database\Encounter
	 * @throws TooManyEncountersException
	 */
	public function getEncounterWithPatientAndType($patientId, $type) {
		$query = $this->repository->createQuery(); $query->filterByType($type);;
		$query->filterByPatientId($patientId);
		
		$count = $this->countRecordsUsingQuery($query);
		
		if($count==0) {
			$encounter = new Encounter();
                        $encounter->setType($type);
			$encounter->setPatient($patientId);
			return $encounter;
		}
		
		if($count>1) {
			throw new TooManyEncountersException("Expecting single encounter with patient ID '$patientId' and type '$type'");
		}
		$query->addEncounterFields("*");
		$query->addObservationFields("*");
		
		return $this->retrieveFirstRecordUsingQuery($query);
	}
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @param \Clinicle\Database\Encounter $childEncounter
	 * @return \Clinicle\Database\Encounter
	 */
	public function getSectionEncountersWithPatientAndType($patientId, $type, $section, $badge=null) {
		if(is_object($section)) {
			$section = $section->getId();
		}
		$query = $this->repository->createQuery(); $query->filterByType($type);;

		$query->filterByPatientId($patientId);
		
		$query->filterBySectionId($section);
        if($badge) {
            $query->filterByEncounterField("badge",$badge);
        }
		$query->addEncounterFields("*");
		$query->addObservationFields("*");
        $collection = $this->retrieveRecordsUsingQuery($query);
		return $collection;
	}
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @param \Clinicle\Database\Encounter $childEncounter
	 * @return \Clinicle\Database\Encounter
	 */
	public function getSectionEncounterWithPatientAndType($patientId, $type,$section, $badge=null) {
		if(is_object($section)) {
			$section = $section->getId();
		}
		$query = $this->repository->createQuery(); $query->filterByType($type);;

		$query->filterByPatientId($patientId);
		$query->filterBySectionId($section);
		$query->addEncounterFields("*");
		$query->addObservationFields("*");
        if($badge) {
            $query->filterByEncounterField("badge",$badge);
        }
        $collection = $this->retrieveRecordsUsingQuery($query);
		
		$count = $collection->count();
		if($count>1) {
			throw new TooManyEncountersException("Expecting single encounter with patient ID '$patientId', type '$type' and section '$section'");
		}
		if($count==0) {
			$encounter = new Encounter($type);
			$encounter->setPatient($patientId);
			$encounter->setSection($section);
			return $encounter;
		}
		
		return $collection->next();
	}
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @param \Clinicle\Database\Encounter $childEncounter
	 * @return \Clinicle\Database\Encounter
	 */
	public function getSectionEncounterWithType(SectionRecord $section, $type) {
	    return $this->getSectionEncounterWithPatientAndType($section->getPatient(), $type, $section);
	}
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @return \ClinicLE\Database\Encounter
	 * @throws TooManyEncountersException
	 */
	public function getSingleEncounterWithPatientTypeAndId($patientId, $type, $id, $badge=null) {
		$query = $this->repository->createQuery(); $query->filterByType($type);;
		$query->filterByPatientId($patientId);
		$query->filterById($id);
		if($badge) {
            $query->filterByEncounterField("badge",$badge);
		}
		$count = $this->countRecordsUsingQuery($query);
		
		if($count==0) {
			return null;
		}
		
		if($count>1) {
			throw new TooManyEncountersException("Expecting single encounter with patient ID '$patientId' and type '$type'");
		}
		
		$query->addEncounterFields("*");
		$query->addObservationFields("*");
		
		$collection = $this->retrieveRecordsUsingQuery($query);
		
		return $collection->next();
	}
	
	/**
	 * 
	 * @param type $patientId
	 * @param type $type
	 * @return \ClinicLE\Database\Encounter
	 * @throws TooManyEncountersException
	 */
	public function getSingleEncounterWithPatientAndType($patientId, $type) {
		$query = $this->repository->createQuery(); $query->filterByType($type);;
		$query->filterByPatientId($patientId);
		
		$count = $this->countRecordsUsingQuery($query);
		
		if($count==0) {
			$encounter = new Encounter($type);
			$encounter->setPatient($patientId);
			return $encounter;
		}
		
		if($count>1) {
			throw new TooManyEncountersException("Expecting single encounter with patient ID '$patientId' and type '$type'");
		}
		
		$query->addEncounterFields("*");
		$query->addObservationFields("*");
		
		$collection = $this->retrieveRecordsUsingQuery($query);
		
		return $collection->next();
	}
        
        /**
	 * 
	 * @param \Clinicle\Database\AbstractEncounterQuery $query
	 * @return \Clinicle\Database\EncounterCollection
	 */
	public function retrieveRecordsUsingQuery(AbstractEncounterQuery $query) {
		list($sql, $params) = $query->generateFullSQL();

		$rows = $this->repository->getConnection()->fetchAll($sql, $params);

		$collection = new EncounterCollection($rows, $query->getTypeFilter());
		return $collection;
	}

	/**
	 * 
	 * @param \Clinicle\Database\AbstractEncounterQuery $query
	 * @return \Clinicle\Database\EncounterCollection
	 */
	public function retrievePageUsingQuery(AbstractEncounterQuery $query, $pageNo = 1, $rowsPerPage = 20) {
		list($sql, $params) = $query->generateFullSQL();

		$rows = $this->repository->getConnection()->fetchPage($sql, $params, $pageNo, $rowsPerPage);

		$collection = new PagedEncounterCollection($rows, $query->getTypeFilter());
                $collection->setPageNo($pageNo);
		$collection->setRows($rows);
		return $collection;
	}
	
	/**
	 * 
	 * @param \Clinicle\Database\AbstractEncounterQuery $query
	 * @return \Clinicle\Database\EncounterCollection
	 */
	public function retrieveSingleRecordUsingQuery(AbstractEncounterQuery $query) {
		list($sql, $params) = $query->generateFullSQL();
		$rows = $this->repository->getConnection()->fetchPage($sql, $params, 1,1);
		if(count($rows)==0) {
			return null;
		}
		$encounter = new EncounterRecord();
		$encounter->setType($query->getTypeFilter());
		$encounter->setAllData($rows[0]);
		return $encounter;
	}

	/**
	 * 
	 * @param \Clinicle\Database\AbstractEncounterQuery $query
	 * @return type
	 */
	public function countRecordsUsingQuery(AbstractEncounterQuery $query) {
		list($sql, $params) = $query->generateCountSQL();
		return $this->repository->getConnection()->fetchSingleValue($sql, $params);
	}

	public function deleteRecordsUsingQuery(AbstractEncounterQuery $query) {
		list($sql, $params) = $query->generateDeleteSQL();
		return $this->repository->getConnection()->execute($sql, $params);
	}
      
}
