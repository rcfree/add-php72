<?php

class ADD_Component_PredictedQmvc extends ADD_Component implements ADD_Component_AjaxInterface {
	function beforeGenerate() {
            $page = $this->_page;
		$form = $page -> getComponent('Form');
                $predPcQmvc = $page->getSetting('PredictedQmvc.field');
                if (!$predPcQmvc) {
                    $page->addWarning("No 'PredictedQmvc.field' provided in page.ini");
                    return;
                }
		$field = $form -> getField($predPcQmvc);
                if (!$field) {
                    $page->addWarning("'PredictedQmvc.field' provided in page.ini does not exist");
                    return;
                }
                $field-> setSetting('viewonly', 1);
	}

	function ajaxFunction($values, ADD_Page $callPage) {
            $page = $this->_page;
            return ADD_Component_PredictedQmvc::calcScoreValues($values, $callPage);
	}
	
	function ajaxInputs(ADD_Page $callPage) {
            return array();
	}
	
	static function calcScoreValues($values, $page) {
		$add = ADD::sys();
		$pid = $add -> getPatientId();
		$pdb = ADD::db('patient');
		$predPcQmvc = $page->getSetting('PredictedQmvc.field');
                
        $genericData = ADD_Field_Util::getSiblingObs($page, 'generic', array('assessment_date'), "populate % predicted QMVC", $values);
		$epData = ADD_Field_Util::getSiblingObs($page, 'expulmdis', array('tot_lean_body_mass'), "populate % predicted QMVC", $values);
    $emrData = ADD_Field_Util::getSiblingObs($page, 'earlymortrisk', array('height'), "populate % predicted QMVC", $values);
		if (!$genericData || !$epData || !$emrData) {
			return array($predPcQmvc => null);
		}
		list($assessDate) = $genericData;
		list($ffmi) = $epData;
   list($height)=$emrData;
		$l = ADD::log();
		$patient = $pdb -> getPatient($pid);
		$dob = $patient['date_of_birth'];
		$gender = $patient['gender'];
		if ($gender!="M" && $gender!="F") {
			$page->addWarning("Gender is not Male or Female. Unable to calculate predicted lung function values.");
			return array($page->getSetting('PredictedQmvc.field')=>"");
		}
		
		$isMale = $gender == "M" ? true : false;
		
		if (!$assessDate)
			return;
			
		$age = ADD_Field_Util::age($dob, $assessDate);
		
		if (!$age) {
			$page -> addWarning("Age could not be calculated. There may be a problem with the date of birth value.");
			return  array($page->getSetting('PredictedQmvc.field')=>"");
		}
                $calc = self::calcPredQmvc($age,$ffmi,$height,$isMale);
                
            return array($predPcQmvc => round($calc,2));
	}
        static function calcPredQmvc($age,$ffm,$height,$isMale) {
            $corrFactor = $isMale ? 0 : -3.42;
            $calc = 56.2 - (0.306 * $age) + (0.686 * $ffm) - (0.156 * $height) + $corrFactor;
            return $calc;
        }
            
}
?>
