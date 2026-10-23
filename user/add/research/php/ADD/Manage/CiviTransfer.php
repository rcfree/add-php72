<?php

class ADD_Manage_CiviTransfer implements ADD_ManageInterface {

    function getDescription() {
        return "CiviCRM research recruit transfer";
    }
    function getOptsList() {
        return "p::";
    }

    function manage($options) {
    	$tp=null;
    	try {
	        $pid = ADD_Util::val('p',$options);
	        $add = ADD::sys();
	        $add->initModules();
	        $add->setPatientId($pid);
	        

			$rconfig = $add->getConfig()->getSection('RecruitPatient');
	        $url = $rconfig->assertSetting('civiUrl');
	        $apiKey = $rconfig->assertSetting('apiKey');
	        $siteKey = $rconfig->assertSetting('siteKey');
	
	        $ca = new ADD_Tool_CiviAccess($url, $siteKey, $apiKey);
	
	        $tp = new ADD_Tool_TransferPatient($ca);

            $this->_transfer($pid,$tp,false);
        }
        catch(Exception $ex) {
        	if($tp) {	
        		$cr = $tp->getConsentRecord();
        		$cr->addLogAndStatus($ex -> getMessage(),"FAILED");
			}
			else {
				$conPage = $add->getPageInModule('research','consent');
				$cr = new ADD_Data_ConsentRecord($conPage->getEncounterRecord());
				$cr->addLogAndStatus($ex -> getMessage(),"FAILED");
				
			}
			$cr->refresh();
			ADD::log()->info("Final CR:".print_r($cr,1));
			
            throw $ex;
        }
    }

    function _transfer($pid,$tp,$ignoreExists=false) {
        ADD::sys() -> setPatientId($pid);

        list($consDate,$consVersion,$consRecruitDb,$consResearchDb) = $tp->assertConsentPresent();

        $tp->loadActivityTypes();
        $tp -> assertPatientExists();
        if (!$ignoreExists) $tp -> assertContactDoesNotExist();
        $recruitId = $tp -> createRecruitContact();
        $tp->addContactToADDGroups($recruitId);
        $tp -> createContactAddress($recruitId);
        $tp -> createContactPhone($recruitId);
        $tp -> createContactEmail($recruitId);
        $gpId = $tp->getGpContact();
        if ($gpId) $tp -> createRecruitGpRelationship($recruitId,$gpId);


        $tp -> createCase($recruitId);

        $tp -> populateCaseActivities();

        $tp-> updateCaseActivity("Consent to be added to recruitment DB", "Recruitment consent",array(
            "date_consent_given"=>$consDate,
            "consent_version"=>$consVersion,
            "consent_for_recruitment_database"=>$consRecruitDb
        ),"Completed");


        $tp-> updateCaseActivity("Consent to be added to research DB", "Research consent",array(
            "date_consent_given"=>$consDate,
            "consent_version"=>$consVersion,
            "consent_for_research_database"=>$consResearchDb
        ),"Completed");

        $tp -> getConsentRecord()->addLogAndStatus("Patient successfully recruited into CiviCRM","SUCCESS",$recruitId);
    }

} 