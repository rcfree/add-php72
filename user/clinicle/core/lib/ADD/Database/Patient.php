<?php



class ADD_Database_Patient extends ADD_Database {

    private $_patients = array();

    function doesPatientExist($patientId, $includeDeleted = false) {
        $patient = $this->getPatient($patientId);
        if (!$includeDeleted) {
            if ($patient && $patient['is_deleted'])
                return false;
        }
        if ($patient)
            return true;
        return false;
    }
   
    function isPatientDeleted($patientId) {
        $patient = $this->getPatient($patientId);
        return $patient ? $patient['is_deleted'] : false;
    }

    function noPatients() {
        return $this->countPatients() == 0 ? true : false;
    }

    function _createPatient($pdata) {
        $patient = $this->insert("patient", $pdata,array("date_created","date_updated"));
        return $patient;
    }

    function assertRequired($data,$fields) {
        foreach($fields as $field) {
            if (strlen(ADD_Util::val($field,$data))==0) {
                throw new ADD_DatabaseException("Cannot create patient. No ".$field." field was provided");
            }
        }
    }
    function createPatientDemographics($pdata) {
        $patientData = $this->matchDataToTable("patient", $pdata);
        $addressData = $this->matchDataToTable("address", $pdata);
        $this->convertPatientDatesToDb($patientData);
        $gpData = $this->matchDataToTable("gp", $pdata);
        
        $this->assertRequired($patientData,array("identifier","first_name","last_name","date_of_birth"));
        
        try {
            $address = null;
            if (count($addressData) > 0) {
                $address = $this->insert("address", $addressData,array("date_created","date_updated"));
            }
            $gp = null;
            if (count($gpData) > 0) {
                $gp = $this->insert("gp", $gpData,array("date_created","date_updated"));
            }
            if ($address)
                $patientData['address_id'] = $address['id'];
            if ($gp)
                $patientData['gp_id'] = $gp['id'];
            $patient = $this->_createPatient($patientData);

            if ($address) {
                $address['patient_id'] = $patient['id'];
                $this->update("address", $address,null, "date_updated");
            }
        } catch (PDOException $ex) {
            if (preg_match("/Duplicate entry/", $ex->getMessage())) {
                throw new ADD_PatientExistsException("Unable to create patient. Another patient containing one or more of the same unique IDs was found in ADD.");
            }
            if (preg_match("/String data, right truncated.+column \'(.+)\'/",$ex->getMessage(),$matches)) {
                $truncField = $matches[1];
                $pdata[$truncField]=substr($pdata[$truncField],0,$this->getMaxLength("patient",$truncField));
                ADD::web()->addWarning("Patient saved but '".$truncField."' field was truncated (entry was larger than the allowed size)");
                
                return $this->createPatientDemographics($pdata);
            }
            throw $ex;
        } catch (Exception $ex) {
            throw $ex;
        }
        return array($patient, $address, $gp);
    }

    function updatePatientDemographics($patientId, $pgadata, $patientOnly=false) {
        list($p, $a, $g) = $this->getPatientDemographics($patientId);
        $pdata = $this->matchDataToTable("patient", $pgadata);
        
        $this->convertPatientDatesToDb($pdata);
        
        $adata = $this->matchDataToTable("address", $pgadata);
        $gdata = $this->matchDataToTable("gp", $pgadata);

        list ($patient, $addr, $gp) = null;
        if ($adata) {
            $addr = $this->update("address", $a, $adata,"date_updated");
        } else {
        	$adata['patient_id'] = $patientId;
            $addr = $this->insert("address", $adata,null,array("date_created","date_updated"));
            $pdata['address_id'] = $addr['id'];
        }
        
        if ($gdata) {
            $gp = $this->update("gp", $g, $gdata,"date_updated");
        } else {
            $gdata['patient_id'] = $patientId;
            $gp = $this->insert("gp", $gdata,array("date_created","date_updated"));
            $pdata['gp_id'] = $gp['id'];
        }
        
        $patient = $this->update("patient", $patientId,$pdata,"date_updated");
        return array($patient, $addr, $gp);
    }


    function getPatient($id) {
		return $this->getFromCacheFunction("_getPatient", array($id));
        
    }
	function _getPatient($id) {
		$patient = $this->selectById("patient", $id);
        $patient = $this->convertPatientDatesFromDb($patient);
        return $patient;
	}
    
    function getPatientDemographics($patientId, $singleArray = false) {
        if (!$patientId)
            throw new ADD_DatabaseException("No patient ID provided to getPatientAndAddressAsArray()");

        if ($singleArray) {
            $p = $this->fetchFirst(
                    "select * from patient p left join address a on p.address_id = a.id left join gp g on p.gp_id = g.id where p.id=?", array($patientId)
            );
            return $p;
        }

        $patient = $this->selectById("patient", $patientId);

        if (!$patient)
            return null;
        
        $this->convertPatientDatesFromDb($patient);
        
        $address = $this->selectById("address", $patient['address_id']);
        $gp = $this->selectById("gp", $patient['gp_id']);

        return array($patient, $address, $gp);
    }

    function getPatientWithIdentifier($identifier) {
        $patient = $this->fetchFirst("select p.* from patient p left join address a  on p.address_id = a.id where p.identifier=?", $identifier);
        return $this->convertPatientDatesFromDb($patient);
    }

    function convertPatientDatesFromDb(&$patient) {
        ADD_Util::convertDateFromDb($patient, array("date_of_birth", "date_of_death"));
        return $patient;
    }

    function convertPatientDatesToDb(&$patient) {
        ADD_Util::convertDateToDb($patient, array("date_of_birth", "date_of_death"));
        return $patient;
    }

    function getPatientWithNhsNo($nhsNo) {
        $pq = PatientQuery::create();
        return $pq->findOneByNhsNo($nhsNo);
    }

    function purgePatient($patient) {
        $patId = is_array($patient) ? $patient['id'] : $patient;
        // $patient = $this -> getPatient($pid);
        ADD::db('encounter')->purgePatientEncounters($patId);
        ADD::db('encounter')->execute("delete from section where patient_id=?",$patId);
        $this->deleteByField("patient", "id", $patId);
    }

    function purgeTestPatients() {
        $testPats = $this->fetchAll("select * from patient where identifier like 'Z%';");
        $enc = ADD::db('encounter');
        foreach ($testPats as $testPat) {
            $enc->purgePatientEncounters($testPat['id']);
            $this->purgePatient($testPat);
        }
    }

    function deletePatient($pid) {
        $patient = $this->getPatient($pid);
        $this->update("patient",$patient,array("is_deleted"=>1),"date_updated");
        return $patient;
    }

}

?>