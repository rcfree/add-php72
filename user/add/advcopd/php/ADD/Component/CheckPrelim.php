<?php
class ADD_Component_CheckPrelim extends ADD_Component {
	function beforeGenerate() {
            $page = $this->_page;
		$er = $page->getEncounterRecord();
		if (!$er->exists()) return;
		$firstSect = ADD_Component_CheckPrelim::countEqual($er,array('post_bronch_fev1_gt_50','fev1_fvc_lt_0_7'),"Y");
		$secondSect = ADD_Component_CheckPrelim::countEqual($er,array(
		'est_resp_failure','adm_hosp_exac_copd','severe_disability','assess_vol_reduc_therapies','low_bmi_or_weight_loss','active_smoker')
		,"Y");
		if ($firstSect >= 1 and $secondSect >=1) {
			$page->addMessage("Patient DOES fulfill criteria for advanced COPD clinic");
		}
		else {
			$page->addWarning("Patient DOES NOT fulfill criteria for advanced COPD clinic");
		}
	}
	
	static function countEqual($er,$fields,$value) {
		$count = 0;
		foreach($fields as $field) {
			if ($er->getObsValue($field)==$value) {
				$count++;
			}
		}
		return $count;
	}
}
?>
