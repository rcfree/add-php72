<?php
include_once("ADD/Field/Submit.php");
class ADD_Field_Submit_CopyActiveDrugs extends ADD_Field_Submit {
	
	function submitValues($values,$page) {
		$add = ADD::sys();
		$pid = $add->getPatientId();
		$form = $page->getComponent('Form');
                $mod = ADD::sys()->getModule('med');
		$er = $mod->getPage('general')->getEncounterRecord();
                $er->retrieve();
                $em = ADD::sys()->getEncounterManager();
                $cers = $em->children($er,"med-general-drug");
                $per = $page->getParent()->getEncounterRecord();
                
		$copiedCount=0;
		foreach($cers as $cer) {
                    $cer->retrieve();
                    
                    if ($cer->getObsValue('drug_type')!=1) continue;
                    if ($cer->getObsValue('current_status')!=1) continue;
                    $tgtObs = array("adherence_type"=>1,"drug" => $cer->getObsValue('drug_name'), "dose" => $cer->getObsValue('dose'), "frequency" => $cer->getObsValue('frequency'),"drug_encounter_id"=>$cer->getId());
                    $ter = $em->newChild($per, "med-adhassess-record");
                    $ter->store($tgtObs);
                    $copiedCount++;
		}
		$page->addMessage("Copied ".$copiedCount." active drug ".ADD_Util::pluralize($copiedCount,"record")." to this adherence assessment.");
	}

	function getLinkTitle($page) {
		return "<i class='icon-share-alt'></i>&nbsp;".$this->getTitle();
	}
	
}

?>