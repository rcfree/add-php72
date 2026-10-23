<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Module\ModuleCollection;


class ADD_Export_GenerateData {
	private $_modules;
        private $ilhIdentifier;
        
	function __construct($options, ModuleCollection $modules) {
		$add = ADD::sys();
		list($this->rowPerPatient,$this->obsFilter,$this->encTypes,$this->memberOf,$this->dateFrom,$this->dateTo,$this->format, $this->identifierList, $this->researchConsent, $this->recruitmentStatus, $this->removeMemo, $this->ilhIdentifier, $this->namesOnly, $this->sepCheckValues,$this->valuesOnly) = 
		ADD_Util::vals(array('rowPerPatient','obsFilter','encTypes','memberOf','dateFrom','dateTo','format','identifierList','researchConsent','recruitmentStatus','removeMemo','ilhIdentifier','namesOnly', 'sepCheckValues','valuesOnly'),$options);
		$this->_modules = $modules;
		
	}
	
	function outputFile() {
		$log = ADD::log($this);
		$log->info("Start export file");
        $cdb = ADD::sys()->getService("ConsentRecord");
        $cdb->createConsentRecord();
		$encounterLookup = new ADD_Export_EncounterLookup($this->_modules);
		
		$observationDictionary = new ADD_Export_ObservationDictionary($encounterLookup,$this->rowPerPatient);
		$observationDictionary->setUseNamesOnly($this->namesOnly);
        $observationDictionary->setSepCheckValues($this->sepCheckValues);
        $observationDictionary->setFieldValuesOnly($this->valuesOnly);
        
		$outputFile = new ADD_Export_OutputFile($this->obsFilter,$this->format,$this->recruitmentStatus);
		if($this->researchConsent==1) {
		    $outputFile->setPseudonymised(true);
                }
                if($this->recruitmentStatus==1) {
                    $outputFile->setIncludeRecruitment(true);
                }
                
                if($this->ilhIdentifier==1) {
                    $outputFile->setIncludeIlhIdentifier(true);
                }
        
        
		$edb = ADD::db('encounter');
		$psql = "select * from patient";
		$pparams = array();
		if(strlen(trim($this->identifierList))>0) {
			$idList = explode(" ",$this->identifierList);
			foreach($idList as $idItem) {
				$itemp = explode("=",$idItem);
				if(count($itemp)==2) {
					$pparams[$itemp[0]]=$itemp[1];
				}
				else {
					$pparams[$idItem]=$idItem;
				}
			}
			$placeholders = array_fill(0,count(array_keys($pparams)),"?");
			$psql.=" where identifier in (".implode(",",$placeholders).")";
		}
		$patients = $edb->fetchAll($psql,array_keys($pparams));
		ADD::log()->info("psql:$psql, params:".json_encode($pparams));
		$ofstmt = $edb->prepare("select * from observation_field where encounter_form_id=?");

		$esql = "select e.id as id, e.date_created, e.encounter_form_id, ef.type from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where e.patient_id = ? and e.is_deleted=0";
		if($this->dateFrom) {
			$esql.=" and e.date_updated>=?";
		}
		if($this->dateTo) {
			$esql.=" and e.date_updated<=?";
		}
		
		$params = array();
		
		if ($this->encTypes) {
			$etWhere=array_fill(0,count($this->encTypes),"ef.type like ?");
			$esql.=" and (".implode(" or ",$etWhere).")";
		}
				
		$estmt = $edb->prepare($esql);
		$mstmt = $edb->prepare("select count(*) from domain d left join domain_type dt on d.domain_type_id = dt.id where d.patient_id=? and dt.name=?");
		
		$ostmt = $edb->prepare("select observation_field_id, value from observation o where encounter_id=?");
		
        if($this->researchConsent || $this->ilhIdentifier) {
            $cstmt = $edb->prepare("select * from consent_record where patient_id=?");
        }
		$pdb = ADD::db('patient');
		$pcount=0;
		foreach ($patients as $patient) {
			if (strlen(trim($this->memberOf))>0) {
				$mstmt->execute(array($patient['id'],$this->memberOf));
				$count = $mstmt->fetchColumn(0);
				if ($count==0) continue;
			}
			$ilhIdentifier = null;
                        $allowRec = null;
                        $recStatus = null;
                        
			if($this->researchConsent && !$cdb->isPatientConsented($patient['id'])) {
			     continue;
            }
			if($this->ilhIdentifier) {
			     $patient['ilh_identifier'] = $cdb->getPatientIlhIdentifier($patient['id']);
            }
                    if($this->recruitmentStatus) {
			list($patient['research_db'], $patient['recruit_db'], $patient['recruit_status']) = $cdb->getPatientRecruitmentStatus($patient['id']);
            }
			$patient=$pdb->convertPatientDatesFromDb($patient);
			$items = array();
			$params=array($patient['id']);
			if($this->dateFrom) {
				$params[]=$this->dateFrom;
			}
			if($this->dateTo) {
				$params[]=$this->dateTo;
			}
			$params = array_merge($params,$this->encTypes);
			
			$estmt->execute($params);
			$encs = $estmt->fetchAll();
			
			$em = ADD::sys()->getEncounterManager();
			
			$obsValues=array();
			foreach ($encs as $enc) {
				$er = $em->recordById($enc['id']);
				$er->retrieve();
				$er->setType($enc['type']);
				
				if($this->rowPerPatient) {
					$obsValues = $obsValues + $observationDictionary->convertToTitles($er,$obsValues);
				}
				else {
					$obsValues = $observationDictionary->convertToTitles($er,$obsValues);
					$outputFile->addObservationRow($patient, $er, $obsValues,$pparams, $ilhIdentifier);
				}
			}
			if($this->rowPerPatient) {
				$outputFile->addObservationRow($patient, null, $obsValues,$pparams, $ilhIdentifier);
			}
			
			$pcount++;
		}
		
		$outputFile->closeFile();
		$log->info("End export file");
		
		return $outputFile->getFileName();
	}
	
}
