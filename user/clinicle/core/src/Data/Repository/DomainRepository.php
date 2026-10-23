<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Repository;
use ADD;
/**
 * Description of DomainRepository
 *
 * @author rob
 */
class DomainRepository {
        function retrieveDomain($domain, $force=false) {
        $db = ADD::db('encounter');
                $params = array($domain->getType(),$domain->getPatient());
                $encounter = $db->fetchFirst("select d.* from `domain` d left join `domain_type` dt on d.domain_type_id = dt.id where dt.name=? and d.patient_id=?",$params);
                if(!$encounter) {
                        $domain->setId(null);
                        return;
                }
                $domain->setEncounter($encounter);
        }
        
        function storeDomain($domain) {
            	$db = ADD::db('BASE');
		$adb = ADD::db('audit');
		if(!$domain->isNew() && $domain->exists()) {
			$db->update("domain",$domain->getId(),$domain->getEncounter(),array("date_updated"));
			$adb -> auditDomain("update", $this);
			return;
		}
		
		if(!$domain->getEncData('domain_type_id')) {	
			$dtId = $db->fetchSingleValue("select id from domain_type where name=?",array($domain->getType()));
			if(!$dtId) {
				throw new \Exception("Domain type '".$domain->getType()."' does not exist");
			}
			$domain->setEncData('domain_type_id',$dtId);
		}
		$domain->setEncData("patient_id", $domain->getPatient());
		$domain->setEncounter($db->insert("domain", $domain->getEncounter(),array("date_created","date_updated")));
		$adb -> auditDomain("create", $domain);
        }
        
        function deleteDomain($domain) {
        ADD::db('encounter')->update("domain",array("id"=>$domain->getId()),array("is_deleted"=>1),array("date_updated"));
		ADD::db('audit') -> auditDomain("delete", $domain);
	}

    /**
     *
     * @param type $patientId
     * @param type $type
     * @return boolean
     */
    public function isInDomain($patientId, $domainType) {
        $conn = $db = ADD::db('BASE');
        $sql = "select count(*) from `domain` d left join `domain_type` dt on dt.id = d.domain_type_id where d.patient_id=? and dt.name=?";
        $params = array($patientId,$domainType);
        $count = $conn->fetchSingleValue($sql,$params);
        return $count==0 ? false : true;
    }

    /**
     *
     * @param type $patientId
     * @param type $type
     * @return null
     */
    public function addToDomain($patientId, $domainType) {
        $conn = ADD::db('BASE');
        if ($this->isInDomain($patientId,$domainType)) {
            throw new MultipleRecordsException("Patient '$patientId' is already added to domain '$domainType'");
        }
        $domainTypeId = $conn->fetchSingleValue("select id from domain_type where name=?",array($domainType));
        if(!$domainTypeId) {
            throw new DataTypeUndefinedException("Domain type '".$domainType."' does not exist");
        }
        $conn->insert("domain",array("patient_id"=>$patientId,"domain_type_id"=>$domainTypeId));
    }

    public function getDomainType($domainType) {
        return ADD::db('BASE')->fetchFirst("select * from domain_type where name=?",$domainType);
    }
}
