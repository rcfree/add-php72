<?php


class ADD_Component_LungFunction extends ADD_Component implements ADD_Component_AjaxInterface {
	static function fieldSettings() {
		return array('LungFunction.pred_fev1', 'LungFunction.pred_fvc', 'LungFunction.pred_fev1_fvc', 'LungFunction.pred_pef');
	}

	function beforePopulate() {
            $page = $this->_page;
		$form = $page -> getComponent('Form');
		$settings = $page -> getSettingsFromArray(ADD_Component_LungFunction::fieldSettings(), true);
		$lfFields = array();
		foreach ($settings as $name => $fieldName) {
			if (!$fieldName)
				continue;
			$field = $form -> getField($fieldName);
			if(!$field) continue;
			$field -> setSetting('viewonly', 1);
			$lfFields[$name] = $field;
		}
		$this -> lfFields = $lfFields;
	}

	function ajaxFunction($values, ADD_Page $callPage) {
		$lfValues = ADD_Component_LungFunction::calcLungFunctionValues($values, $callPage);
		$settings = $callPage -> getSettingsFromArray(ADD_Component_LungFunction::fieldSettings(), true);
		$lfFields = array();
		foreach ($settings as $name => $fieldName) {
				$value = $lfValues[$name];
				$rounded = $value ? round($value, 2) : "";
				$lfFields[$fieldName] = $rounded;
		}
		return $lfFields;
	}

	function ajaxInputs(ADD_Page $callPage) {
            return array('spirometry_date','cfactor_90pc','height');
	}

	static function calcLungFunctionValues($values, $page) {
		$add = ADD::sys();
		$pid = $add -> getPatientId();
		$db = ADD::db('encounter');
		$pdb = ADD::db('patient');
		$heightSource = "details";
		
		$reqdSettings = array('LungFunction.height_page', 'LungFunction.height_field', 'LungFunction.date_page', 'LungFunction.date_field');
		list($heightPageName, $heightField, $datePageName, $dateField) = $page -> getSettingsFromArray($reqdSettings, false, true);
		list($height) = ADD_Field_Util::getSiblingObs($page, $heightPageName, array($heightField), "calculate predicted lung function values", $values);
		list($firstVisit) = ADD_Field_Util::getSiblingObs($page, $datePageName, array($dateField), "calculate predicted lung function values", $values);
		$hasCfactor = ADD_Util::getArrayValue('cfactor_90pc',$values) ? true : false;
		$patient = $pdb -> getPatient($pid);
		$dob = $patient['date_of_birth'];

		$pred = "LungFunction.pred";

		$nullValues = array("{$pred}_fev1" => null, "{$pred}_fvc" => null, "{$pred}_fev1_fvc" => null, "{$pred}_pef" => null);
		
		if ($firstVisit == "__/__/____")
			$firstVisit = null;
			
		if (!$firstVisit)
			return $nullValues;
		if (!$height)
			return $nullValues;
			
		$age = ADD_Field_Util::age($dob, $firstVisit);
		
		$gender = $patient['gender'];
		if ($gender!="M" && $gender!="F") {
			$page -> addWarning("Gender is not Male or Female. Unable to calculate predicted lung function values.");
			return $nullValues;
		}
		
		if (!$age) {
			$page -> addWarning("Age could not be calculated. There may be a problem with the date of birth value.");
			return $nullValues;
		}
		
		$lfValues = self::calcPredictedValues($pred, $gender,$height,$age,$hasCfactor);
		
		return $lfValues;
	}
	
	static function calcPredictedValues($pred, $gender,$height,$age,$hasCfactor=false) {
		$lfValues = array();
		$cfactor = $hasCfactor ? 0.9 : 1;
		if ($gender == "M") {
			$lfValues["{$pred}_fev1"] = ((0.0430 * $height) - (0.0290 * $age) - 2.490) * $cfactor;
			$lfValues["{$pred}_fvc"] = ((0.0576 * $height) - (0.0260 * $age) - 4.340) * $cfactor;
			$lfValues["{$pred}_fev1_fvc"] = ((-0.18 * $age) + 87.21);
			$lfValues["{$pred}_pef"] = (((0.0614 * $height) - (0.043 * $age) + 0.15) * 60) * $cfactor;
		} elseif ($gender == "F") {
			$lfValues["{$pred}_fev1"] = ((0.0395 * $height) - (0.025 * $age) - 2.600) * $cfactor;
			$lfValues["{$pred}_fvc"] = ((0.0443 * $height) - (0.0260 * $age) - 2.890) * $cfactor;
			$lfValues["{$pred}_fev1_fvc"] = ((-0.19 * $age) + 89.1);
			$lfValues["{$pred}_pef"] = (((0.055 * $height) - (0.03 * $age) - 1.11) * 60) * $cfactor;
		} else {
			
            $lfValues["{$pred}_fev1"] = null;
			$lfValues["{$pred}_fvc"] = null;
			$lfValues["{$pred}_fev1_fvc"] = null;
			$lfValues["{$pred}_pef"] = null;
		}
		return $lfValues;
	}

}
?>