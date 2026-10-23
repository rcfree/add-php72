<?php



class ADD_Tool_TransferPatient {
        private $astatuses = array(
            "Cancelled" => 3,
            "Completed"=>2,
            "Scheduled" => 1
        );
	private $ca = null;
    private $case = null;
    private $activities = array();
	private $conPage = null;
	private $consentRecord = null;
	public $patient = null;
	private $civiLog = null;
	private $activityTypes = array();
	private $_db = null;
        private $gp = null;
        private $address = null;

        static $C_IDENT_FIELD;
        static $C_NHS_NO_FIELD;
        static $C_ILH_NO_FIELD;
        static $C_GP_GNC_FIELD;
		static $C_BEST_TIME_FIELD;
	static $C_STATUS_FIELD;
	
	function __construct($ca) {
            $add = ADD::sys();

            $mod = $add -> getModule('research');
            $page = $mod -> getPage('recruit');
            $this -> conPage = $page -> getSiblingPage('consent');
			$cr = new ADD_Data_ConsentRecord($this -> conPage -> getEncounterRecord());
            $this -> consentRecord = $cr;
			$rconfig = $add->getConfig()->getSection('RecruitPatient');
            self::$C_IDENT_FIELD = $rconfig->getSetting("C_IDENT_FIELD");
            self::$C_NHS_NO_FIELD = $rconfig->getSetting("C_NHS_NO_FIELD");
            self::$C_ILH_NO_FIELD = $rconfig->getSetting("C_ILH_NO_FIELD");
            self::$C_GP_GNC_FIELD = $rconfig->getSetting("C_GP_GNC_FIELD");
			self::$C_BEST_TIME_FIELD = $rconfig->getSetting("C_BEST_TIME_FIELD");
			self::$C_STATUS_FIELD = $rconfig->getSetting("C_STATUS_FIELD");
            $this->ca = $ca;

            $this ->_db = ADD::db();
	}
   	function getConsentRecord() {
   		return $this->consentRecord;
	}
	
	function ynToBoolean($yn) {
		if ($yn=="Y") return 1;
		if ($yn=="N") return 0;
		return "";
	}
    function genderToCivi($gender) {
        switch($gender) {
            case "M" :
                return 2;
                break;
            case "F" :
                return 1;
                break;
        }

    }
	function assertConsentPresent() {
		$consDate = $this -> get('consent_form_date');
		if (!$consDate)
			throw new ADD_ToolException("Consent date not set in ADD");

		$consDate = substr($consDate, 6, 4) . substr($consDate, 3, 2) . substr($consDate, 0, 2);

		$consVersion = $this -> get('consent_version');
		if (strlen($consVersion) == 0)
			throw new ADD_ToolException("Consent version not set in ADD");

		$initStatus = $this -> get('init_status');
		if (strlen($initStatus) == 0)
			throw new ADD_ToolException("Initial status not set in ADD");
		
		if ($this -> get('init_status')==2) {
			$consRecruitDb='0';
			$consResearchDb='0';
		}
		else {
			$consRecruitDb =$this -> get('add_to_recruit_db');
			if (!$consRecruitDb)
				throw new ADD_ToolException("Recruitment database permission not set in ADD");
	
			$consResearchDb = $this -> get('add_to_research_db');
			if (!$consResearchDb)
				throw new ADD_ToolException("Research database permission not set in ADD");
	
			if (!$this -> get('ilh_identifier'))
				throw new ADD_ToolException("Patient has not been given an ILH (respiratory research) ID");
	
			$this -> addLogEntry("Required consent data present");
			$consRecruitDb = $this -> ynToBoolean($consRecruitDb);
			$consResearchDb = $this -> ynToBoolean($consResearchDb);
		}
		
		return array($consDate, $consVersion, $consRecruitDb,$consResearchDb );
	}

	function populateCaseActivities() {
        $case = $this->case;
		$actIds = $case[0]['activities'];
		$acts = array();
                if ($actIds) {
                    foreach($actIds as $actId) {
                            list($act,$actCount,$actId) = $this->ca->get('Activity',array('id'=>$actId));
                            $atId = $act[0]['activity_type_id'];
                            $atName = $this->activityTypes[$atId];
                            $acts[$atName]=$act[0];
                    }
                }
		$this->activities = $acts;
	}
	function updateCaseActivity($activityType, $cgName,$cfValues, $status) {
		$this->addLogEntry("Updating '$activityType' with data");
		$ca = $this->ca;
		$act = ADD_Util::getArrayValue($activityType,$this->activities);
		if (!$act) throw new ADD_ToolException("Expected activity type '$activityType' not found");

		$acId = $act['id'];

		list($cg,$cgCount,$cgId) = $ca->get("CustomGroup",array('extends'=>'Activity','title'=>$cgName));

		foreach($cfValues as $cfName => $cfVal) {
			list($cf,$cfCount,$cfId) = $ca->get("CustomField",array('custom_group_id'=>$cgId,'name'=>$cfName));
			if (!$cfId) throw new ADD_ToolException("No '".$cfName."' custom field was found in the activity");
			$ca->create("CustomValue",array('entity'=>'CustomValue','entity_id'=>$acId,'entity_table'=>'Activity','custom_'.$cfId=>$cfVal));
			$this->addLogEntry("Updated field '$cfName' to '$cfVal'");
		}
                $ca->create("Activity",array('id'=>$acId,'activity_status_id'=>$this->astatuses[$status]));
                $this->addLogEntry("Updated activity status to '$status'");
	}
	function loadActivityTypes() {
		list($ats,$atcount,$atid) = $this->ca->get("ActivityType",array("sequential"=>null));
		$this->activityTypes=$ats;
	}
	function get($name) {
		return $this->consentRecord->getField($name);
	}

	function assertContactDoesNotExist() {
		$patient = $this -> patient;
        if (!$patient) throw new ADD_ToolException("No patient setup in TransferPatient class");

		list($contact, $count) =  $this -> ca -> get('Contact', array("custom_" . self::$C_IDENT_FIELD => $patient['identifier']));

		if ($count == 1) throw new ADD_ToolException("Recruit with the identifier '" . $patient['identifier'] . "' is already present");
        if ($count > 1) throw new ADD_ToolException("Multiple ($count) recruits with the identifier '" . $patient['identifier'] . "' are already present");
        $this->addLogEntry("Contact '" . $patient['identifier'] . "' (".$patient['first_name']." " .$patient['last_name'].") does not already exist in CiviCRM");
	}
    function setPatient($patient) {
        $this->patient = $patient;
    }

	function assertPatientExists() {
		$add = ADD::sys();
        $pid = $add -> getPatientId();
		list($patient,$address,$gp) = ADD::db('patient') -> getPatientDemographics($pid);

		if (!$patient) {
			throw new ADD_ToolException("Patient with ID " . $add -> getPatientId() . " does not exist in ADD");
		}
		$this -> patient = $patient;
        $this-> address = $address;
        $this-> gp =$gp;
	}
    function dateToCivi($date) {
        $date = substr($date,6,4).substr($date,3,2).substr($date,0,2);
        return $date;
    }
	function addLogEntry($entry,$status=null) {
		$this->consentRecord->addLogAndStatus($entry,$status);
	}

	function createRecruitContact() {
		$patient = $this -> patient;
		$gender = $this->genderToCivi($patient['gender']);

		$this -> addLogEntry("Creating Recruit in CiviCRM");
        $dob = $this->dateToCivi($patient['date_of_birth']);
		$data = array(
        	"custom_" . self::$C_IDENT_FIELD => $patient['identifier'], 
        	"custom_" . self::$C_NHS_NO_FIELD => $patient['nhs_no'], 
        	"custom_" . self::$C_ILH_NO_FIELD => $this -> get('ilh_identifier'), 
        	"custom_" . self::$C_BEST_TIME_FIELD => $this -> get('best_time'), 
        	"custom_" . self::$C_STATUS_FIELD => $this -> get('init_status'), 
        	"first_name" => $patient['first_name'], 
        	"last_name" => $patient['last_name'], 
        	"birth_date"=>$dob,
        	"contact_type" => "Individual", 
        	"contact_sub_type" => "Recruit", 
        	"gender" => $gender 
		);
		if ($this->get('init_status')==2) {
			$data['do_not_phone']=1;
			$data['do_not_email']=1;
			$data['do_not_mail']=1;
			$data['do_not_sms']=1;
			$data['do_not_trade']=1;
		}
        $contact = $this -> ca -> create("Contact", $data);
		return $contact[0]['id'];
	}
    function addContactToADDGroups($cid) {
        $encs = $this->_db->fetchAll("select ef.type from encounter e left join encounter_form ef on e.encounter_form_id=ef.id where e.parent_encounter_id is null and e.patient_id=?",array($this->patient['id']));
        $ignoredGroups=array('advcopd-prelim','res');

        foreach($encs as $enc) {
            $type = $enc['type'];
            if (in_array($type,$ignoredGroups)) continue;
            $title = $this->getGroupTitle($type);
            $this->addRecruitToGroup($title,$cid);
        }
    }

    function getGroupTitle($name) {
        $titles = array("dac"=>"Difficult Asthma Clinic","research"=>"Research Recruit","advcopd"=>"Advanced COPD","inv"=>"Investigations","med"=>"Medication","history"=>"Medical history");
        return isset($titles[$name]) ? $titles[$name] : $name;
    }

    function getGpContact() {
		$gp = $this->gp;
                if (!$gp) {
                    $this -> addLogEntry("Could not create recruit/GP relationship - patient does not have any GP data");
                    return null;
                }
		$gncNo = $gp['doh_code'];
                if (!$gncNo) {
                    $this -> addLogEntry("Could not create recruit/GP relationship - patient does not have required GP data");
                    return null;
                }

                $this -> addLogEntry("Retrieve patient's GP '".$gp['doh_code']."' from CiviCRM");

		list($contact,$count,$id) = $this -> ca -> get("Contact", array("custom_" . self::$C_GP_GNC_FIELD => $gncNo, "contact_type" => "Individual", "contact_sub_type" => "GP" ));
                if (!$contact) $this -> addLogEntry("GP with GNC number '".$gncNo."' does not exist in CiviCRM");
                return $id;
	}

	function createContactAddress($cid) {
		$address = $this -> address;
		if ($address) {
			$this -> addLogEntry("Creating Recruit address in CiviCRM");

			$civiaddr = $this -> ca -> create("Address", array('location_type_id' => 3, 'contact_id' => $cid, 'street_address' => $address['address_line_1'], 'supplemental_address_1' => $address['address_line_2'], 'supplemental_address_2' => $address['address_line_3'], 'city' => $address['address_line_4'], 'postal_code' => $address['postcode']));
		} else {
			$this -> addLogEntry("Recruit address not added as there was none in ADD");
		}
	}
    function createGroup($groupTitle) {
        $group = $this->ca->create('Group', array("title"=>$groupTitle));
        return $group[0]['id'];
    }
    function getGroup($groupTitle) {
        list($grp,$gcount,$gid) = $this->ca->get('Group', array("title"=>$groupTitle));
        return $gid;
    }
    function addRecruitToGroup($groupTitle,$cid) {
        $gid = $this->getGroup($groupTitle);
        ADD::log()->info("gid:$gid");
        if (!$gid) {
            $this -> addLogEntry("Creating '$groupTitle' group in CiviCRM");
            $gid = $this->createGroup($groupTitle);
        }
        else {
            $this -> addLogEntry("Getting existing '$groupTitle' group from CiviCRM");
        }
        $this->ca->create("GroupContact",array("group_id"=>$gid, "contact_id"=>$cid));
        $this -> addLogEntry("Add Recruit to '$groupTitle' group");
    }

    function createRecruitGpRelationship($patientId,$gpId) {

        $relNameAB = "GP of";

        list($rt,$rtCount,$rtId) = $this->ca->get('RelationshipType', array("name_a_b"=>$relNameAB));
        $params = array('contact_id_a' => $gpId, 'contact_id_b' => $patientId, 'relationship_type_id' => $rtId, 'is_active' => '1');
        list($rel,$relCount,$relId) = $this->ca->get('Relationship', $params );

        if ($relCount == 0) {
            $this->ca->create('Relationship', $params);
            $this -> addLogEntry("Patient/GP link created");
            return;
        }
        $this -> addLogEntry("Patient/GP link already exists");
    }
	function createContactEmail($cid) {
		if ($this -> get('email_address')) {
			$this -> addLogEntry("Creating Recruit email in CiviCRM");

			$email = $this -> ca -> create("Email", array('contact_id' => $cid, 'location_type_id' => 1, 'email' => $this->get('email_address'), 'is_primary' => 1));
		}
	}
	
	function createContactPhone($cid) {
		$address = $this -> address;
		if ($address  && !ADD_Util::isEmpty($address['home_phone'])) {
			$this -> addLogEntry("Creating phone in CiviCRM");

			$phone = $this -> ca -> create("Phone", array('contact_id' => $cid, 'is_primary' => 1, 'location_type_id' => 1, 'phone_type_id' => 1, 'phone' => $address['home_phone']));
		}
	}

	function createCase($cid) {
		$this -> addLogEntry("Creating Recruitment record for patient");
		$case = $this -> ca -> create('Case', array('case_type' => 'RespiratoryResearch', 'contact_id' => $cid, 'creator_id' => 1, 'case_subject' => 'Automatically added to CiviCRM by ADD','return'=>'Activities'));
		list($c,$ccount,$cid) = $this->ca->get('Case',array('id'=>$case[0]['id']));
		$this->case = $c;
	}

}
?>