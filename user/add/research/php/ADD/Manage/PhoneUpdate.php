<?php

class ADD_Manage_PhoneUpdate implements ADD_ManageInterface {
	
    function getDescription() {
        return "Copy demographics phone number to details";
    }
    function getOptsList() {
        return "";
    }

    function manage($options) {
		
		
    	
		
		
		
		ADD::sys()->initModules();
		$encounterLookup = new ADD_Export_EncounterLookup(ADD::sys()->getModules());
		$result = new ADD_Query_Result($encounterLookup);
		$output = new ADD_Query_Output_Simple($run,$result);
		$run = new ADD_Query_Run();
		$run->addFilter("Patient",array("field"=>"deceased","value"=>"1","op"=>"not_equal"));
		$run->addValue("PatientData",array("fields"=>array("identifier","home_phone")));
		$run->addValue("ObsData",array("fields"=>array("current_status","phone_number","ilh_identifier"),"encounter"=>"res-contact-details"));
		
		$em = ADD::sys()->getEncounterManager();
		$run->beforeRun($result,$output);
		
		while($item = $run->nextRowItemOutput($result,$output)) {
			$encData = $item->getEncounterData("res-contact-details");
			if(ADD_Util::isEmpty($encData)) continue;
			$encIds = array_keys($encData);
			$encId = $encIds[0];
			$er = $em->recordById($encId);
			$er->retrieve();
			$homePhone = $item->getPatientData("home_phone");
			if(!ADD_Util::isEmpty($er->getObsValue("phone_number"))) {
				ADD::log()->warn("PATIENT NOT UPDATED (".$item->getPatientData("identifier").") number already set to ".$er->getObsValue("phone_number"));
				continue;
			}
			if(ADD_Util::isEmpty($homePhone)) continue;
			
			$er->setObsValue("phone_number",$homePhone);
			$er->store();
			ADD::log()->warn("PATIENT UPDATED (".$item->getPatientData("identifier").") with number ".$homePhone);
		}
    }

} 