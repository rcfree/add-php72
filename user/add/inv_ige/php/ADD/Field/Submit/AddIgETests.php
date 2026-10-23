<?php

class ADD_Field_Submit_AddIgETests extends ADD_Field_Submit {
	
	function submitValues($values,$page) {
		if (!$this->isSubmittedField($values)) return;

		$add = ADD::sys();
		$ddb = ADD::db('dictionary');
		$edb = ADD::db('encounter');
		$adb = ADD::db('audit');
		
		$ppage = $page->getParentPage();
		$pencComp = $ppage->getComponent('Encounter');
		list($penc,$pobs) = $pencComp->retrieveEncounter($ppage);
		$encType = $page->getSetting('encounter');
		$rowEncounters = array();
                $groups = explode(";", $this->getSetting('groups'));
                if (!$groups) { 
                    $page->addWarning("'groups' not defined in field settings");
                    return;
                }
                $items = array();
                foreach($groups as $group) {
                    $items = $items + $ddb->getDictionaryGroupItems($group);
                }
		
                $obsMaps = $this->getSetting('obs_map');
		$cobs = array();
		foreach($obsMaps as $obsMap) {
			list($source,$target) = explode(":",$obsMap);
			$cobs[$target]=$values[$source];
		}
                $gfield = $this->getSetting('group_field');
                if (!$gfield) {
                    $page->addWarning("'group_field' not defined in field settings");
                    return;
                }
		foreach($items as $value=>$title) {
			$cobs['result']='';
                        $cobs[$this->getSetting('group_field')]=$value;
			$cenc = $edb->createChildEncounterWithTypeAndObservations(
					$penc,
					$encType,
					$cobs
			);

			$adb->auditEncounter("create",$cenc,$cenc->getObservations());	
		}
		$page->addMessage("Added standard IgG test panel");
	}

	function getLinkTitle($page) {
		return "<i class='icon-ok'></i>&nbsp;".$this->getTitle();
	}
	
}

?>