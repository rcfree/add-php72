<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Search
 *
 * @author rcf8
 */
class ADD_Database_SearchPatient extends ADD_Database {
    function findPatientsMatchingTerms($terms, $pg = 1, $rows = 20) {
        
		$params = array();
        $where = array();
		
        foreach (explode(" ", $terms) as $term) {
			$dt = ADD_Util::dateFromDmy($term);
        	
			if ($dt) {
				$where[]= "p.date_of_birth=?";
                                $params[] = $dt->format("Y-m-d");
			}
			else {
                            $where[]="(p.first_name like ? or p.last_name like ? or p.identifier like ? or p.nhs_no like ? or p.hospital_no like ?)";
                            $params = array_merge($params, array_fill(0, 5, "%{$term}%"));
			}
        }
        
        $sql = "from patient p left join address a on p.address_id = a.id where ";
       	if (count($where)>0) $sql.=implode(" or ",$where);
        $records = $this->fetchPage("select p.*, a.address_line_1 $sql", $params, $pg, $rows);
        $count = $this->fetchSingleValue("select count(*) $sql", $params, $pg, $rows);
        $patients = array();
        foreach($records as $record) {
            $patients[] = ADD::db('patient')->convertPatientDatesFromDb($record);
        }
        $first = ($pg*$rows)-$rows + 1;
        $last = $first + count($patients)-1;
        return array("data"=>$patients,"count"=>$count,"first"=>$first,"last"=>$last);
    }

    function findPatientsMatchingNameAndDob($params, $pg = 1, $rows = 20) {
       	ADD_Util::convertDateToDb($params,array("dob"));
           
	    list($fname,$lname,$dob) = ADD_Util::vals(array("first_name","last_name","dob"),$params);
        $sql = "from patient p left join address a on p.address_id = a.id";
       
        
        $where = array();
        $params = array();
        if ($dob) {
        	 $where[]="p.date_of_birth=?";	
            $params[] = $dob;
        }
		
        $where[]="p.first_name like ?";
        $where[]="p.last_name like ?";
        $params[]="%$fname%";
        $params[]="%$lname%";

        if (count($where)>0) $sql.=" where ".implode(" and ",$where);
        $records = $this->fetchPage("select p.*, a.address_line_1 $sql", $params, $pg, $rows);
        $patients = array();
        foreach($records as $record) {
            $patients[] = ADD::db('patient')->convertPatientDatesFromDb($record);
        }

        $count = $this->fetchSingleValue("select count(*) $sql", $params, $pg, $rows);
        $first = ($pg*$rows)-$rows + 1;
        $last = $first + count($patients)-1;
        return array("data"=>$patients,"count"=>$count,"first"=>$first,"last"=>$last);
    }
    
    function findExistingPatientIds($patData, $patientId) {
        list($ident, $nhsNo) = ADD_Util::vals(array('identifier', 'nhs_no'), $patData);
        
        $patFields = array();
        if ($ident) $patFields['identifier'] = $this->selectAllByField("patient","identifier",$ident);
        if ($nhsNo) $patFields['nhs_no'] = $this->selectAllByField("patient","nhs_no",$nhsNo);
        
        $patMatchFields = array();
         
        foreach ($patFields as $field => $patients) {
            if ($patients) {
                foreach ($patients as $patient) {
                    $currPid = $patient['id'];
                    if ($currPid == $patientId)
                        continue;
                    if (!isset($patMatchFields[$currPid]))
                        $patMatchFields[$currPid] = array();
                    $patMatchFields[$currPid][] = $field;
                }
            }
        }
        return $patMatchFields;
    }
    
     
    function doPatientsExistWithIdentifiers($patient) {
        $sql = "select count(*) from patient p where p.identifier=? or p.nhs_no=?";
        $params = array(ADD_Util::val('identifier', $patient), ADD_Util::val('nhs_no', $patient));
        $count = $this->fetchSingleValue($sql, $params);
        return $count > 0 ? true : false;
    }    
}

