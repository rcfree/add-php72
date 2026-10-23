<?php

class ADD_Field_Submit_CopyActiveDrugs extends ADD_Field_Submit {
	
	function submitValues($values,$page) {
	    CLE::info("Copy active drugs",$values);

		$add = ADD::sys();
		$pid = $add->getPatientId();
        CLE::info("Patient id:".$pid);
        $sectionId = $add->getParam('eid');
        $parentPim = $this->getSetting("parent_pim","med:general");
		$parentPage = ADD::sys()->getPageFromPim($parentPim);
        if(!$page) {
            throw new ADD_PageException("Page with pim ".$parentPim." is not defined");
        }
		$er = $parentPage->getEncounterRecord();
        $er->retrieve();
        $em = ADD::sys()->getEncounterManager();
        $cers = $em->children($er,"med-general-drug");
        $per = $em->sectionById($sectionId);
        $per->retrieve();

		$copiedCount=0;
		foreach($cers as $cer) {
                    $cer->retrieve();
                    if(!$this->doIncludeRecord($cer)) {
                        continue;
                    }
                    $tgtObs = array("adherence_type"=>1,"drug" => $cer->getObsValue('drug_name'), "dose" => $cer->getObsValue('dose'), "frequency" => $cer->getObsValue('frequency'),"drug_encounter_id"=>$cer->getId());
                    $ter = $em->newChild($per, "med-adhassess-record");
                    $ter->store($tgtObs);
                    $copiedCount++;
		}
		$page->addMessage("Copied ".$copiedCount." active drug ".ADD_Util::pluralize($copiedCount,"record")." to this adherence assessment.");
	}
    function doIncludeRecord($er) {
        if ($er->getObsValue('drug_type')!=1) {
            return false;
        }
        if ($er->getObsValue('current_status')!=1) {
            return false;
        }
        return true;
    }
                    
	function getLinkTitle($page) {
		return "<i class='icon-share-alt'></i>&nbsp;".$this->getTitle();
	}
	
}

?>