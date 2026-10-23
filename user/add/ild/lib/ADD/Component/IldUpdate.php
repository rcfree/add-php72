<?php


class ADD_Component_IldUpdate extends ADD_Component {
	const ILD_VALUE=7;
	function afterSubmit(ADD_Page $submitPage) {
		if($submitPage!==$this->_page) return;
		$add = ADD::sys();
		$ildRecord = $submitPage->getEncounterRecord();
		$ildRecord->retrieve();
		$include = $ildRecord->getObsValue("fib_ild") == "Y" ? true : false;
	
		$summaryPage = $add->getPageFromPim('history:summary');
		$summaryRecord = $summaryPage->getEncounterRecord();
		$this->updatePCD($summaryRecord,$include);
	}
	function updatePCD($summaryRecord,$include) {
		if(!$summaryRecord->exists()) {
			$pcdData = array();
		}
		else {
			$summaryRecord->retrieve();
			$pcdSerial = $summaryRecord->getObsValue("primary_cond_dis");
			$pcdData = ADD_Util::unserialize($pcdSerial);
			if(!$pcdData) {
				$pcdData = array();
			}
		}
		if(isset($pcdData[self::ILD_VALUE])) {
			if($include) {
				return;
			}
			else {
				unset($pcdData[self::ILD_VALUE]);
			}
		}
		else {
			if($include) {
				$pcdData[self::ILD_VALUE]=self::ILD_VALUE;
			}
		}
		$summaryRecord->store(array("primary_cond_dis"=>$pcdData));
		
		$messageText = $include ? "Added ILD to the" : "Removed ILD from the";
		ADD::web()->addMessage($messageText ." 'Condition summary' (medical history) page");
	}
}
?>