<?php


class ADD_Component_FraminghamRisk extends ADD_Component implements ADD_Component_AjaxInterface {
	private static $framScoreField = "framingham_score";
	private static $framChdRiskField = "framingham_chd10yr_risk";

	function beforeGenerate() {
            $page = $this->_page;
		$form = $page -> getComponent('Form');
		$form -> getField(self::$framScoreField) -> setSetting('viewonly', 1);
		$form -> getField(self::$framChdRiskField) -> setSetting('viewonly', 1);
	}

	function ajaxFunction($values,ADD_Page $callPage) {
            return ADD_Component_FraminghamRisk::calcScoreValues($values, $callPage);
	}
	
	function ajaxInputs(ADD_Page $callPage) {
            return array('total_cholesterol', 'hdl_cholesterol', 'systolic_bp', 'treated_for_hypertension');
	}
	
	static function calcScoreValues($values, $page) {
		$add = ADD::sys();
		$pid = $add -> getPatientId();
		$pdb = ADD::db('patient');
		//$parent = $page->getParentPage();
		$genericData = ADD_Field_Util::getSiblingObs($page, 'generic', array('assessment_date', 'smoking_history'), "populate Framingham risk score", $values);
		$epData = ADD_Field_Util::getSiblingObs($page, 'expulmdis', array('total_cholesterol', 'hdl_cholesterol', 'systolic_bp', 'treated_for_hypertension'), "populate Framingham risk score", $values);
		if (!$genericData || !$epData) {
			return array(self::$framScoreField => null, self::$framChdRiskField => null);
		}
		list($assessDate, $smokingHistory) = $genericData;
		list($totalChol, $hdlChol, $systBloodPressure, $treatedForHt) = $epData;
		$l = ADD::log();
		$patient = $pdb -> getPatient($pid);
		$dob = $patient['date_of_birth'];
		$gender = $patient['gender'];
                if ($gender!="M" && $gender!="F") {
                    $page->addWarning("Gender is not Male or Female. Unable to calculate predicted lung function values.");
                    return array(self::$framScoreField => null, self::$framChdRiskField => null);
                }
		$isMale = $gender == "M" ? true : false;
		$isCigSmoker = $smokingHistory == "2" ? true : false;
		$isTreated = $treatedForHt == "Y" ? true : false;

		if (!$assessDate)
			return;
		$age = ADD_Field_Util::age($dob, $assessDate);
                if (!$age) {
			$page -> addWarning("Age could not be calculated. There may be a problem with the date of birth value.");
			return array(self::$framScoreField => null, self::$framChdRiskField => null);
		}
                if ($age>79) {
                    $page -> addWarning("Framingham score cannot be calculated - patient is greater than 79 years old.");
                }
		list($points, $risk) = self::calcFramScore($isMale, $age, $totalChol, $isCigSmoker, $hdlChol, $isTreated, $systBloodPressure);
		
                return array(self::$framScoreField => $points, self::$framChdRiskField => $risk);
	}

	static function getAgeTestData($isMale) {
		if ($isMale) {
			return array("-9" => array(20, 34), "-4" => array(35, 39), "0" => array(40, 44), "3" => array(45, 49), "6" => array(50, 54), "8" => array(55, 59), "10" => array(60, 64), "11" => array(65, 69), "12" => array(70, 74), "13" => array(75, 79));
		} else {
			return array("-7" => array(20, 34), "-3" => array(35, 39), "0" => array(40, 44), "3" => array(45, 49), "6" => array(50, 54), "8" => array(55, 59), "10" => array(60, 64), "12" => array(65, 69), "14" => array(70, 74), "16" => array(75, 79));
		}
	}

	static function getSmokerData($isMale) {
		if ($isMale) {
			return array("8" => array(20, 39), "5" => array(40, 49), "3" => array(50, 59), "1" => array(60, 69), "1" => array(70, 79));
		} else {
			return array("9" => array(20, 39), "7" => array(40, 49), "4" => array(50, 59), "2" => array(60, 69), "1" => array(70, 79));
		}
	}

	static function getTotalCholTestData($isMale) {
		if ($isMale) {
			return array( array(20, 39, array("0" => array(null, 160), "4" => array(160, 199), "7" => array(200, 239), "9" => array(240, 279), "11" => array(280, null))), array(40, 49, array("0" => array(null, 160), "3" => array(160, 199), "5" => array(200, 239), "6" => array(240, 279), "8" => array(280, null))), array(50, 59, array("0" => array(null, 160), "2" => array(160, 199), "3" => array(200, 239), "4" => array(240, 279), "5" => array(280, null))), array(60, 69, array("0" => array(null, 160), "1" => array(160, 199), "1" => array(200, 239), "2" => array(240, 279), "3" => array(280, null))), array(60, 69, array("0" => array(null, 160), "0" => array(160, 199), "0" => array(200, 239), "1" => array(240, 279), "1" => array(280, null))));
		} else {
			return array( array(20, 39, array("0" => array(null, 160), "4" => array(160, 199), "8" => array(200, 239), "11" => array(240, 279), "13" => array(280, null))), array(40, 49, array("0" => array(null, 160), "3" => array(160, 199), "6" => array(200, 239), "8" => array(240, 279), "10" => array(280, null))), array(50, 59, array("0" => array(null, 160), "2" => array(160, 199), "4" => array(200, 239), "5" => array(240, 279), "7" => array(280, null))), array(60, 69, array("0" => array(null, 160), "1" => array(160, 199), "2" => array(200, 239), "3" => array(240, 279), "4" => array(280, null))), array(60, 69, array("0" => array(null, 160), "1" => array(160, 199), "1" => array(200, 239), "2" => array(240, 279), "2" => array(280, null))));
		}
	}

	static function getHdlCholTestData($isMale) {
		return array("-1" => array(60, null), "0" => array(50, 59), "1" => array(40, 49), "2" => array(null, 40));
	}

	static function calcTestPointScore($compValue, $pointData, $isMap = false) {
		if (count($pointData) == 0)
			throw new ADD_ComponentException("PointData is empty (compValue:$compValue)");
		foreach ($pointData as $points => $pointRange) {
			if (!$isMap)
				$points = array_shift($pointRange);
			if ($pointRange[0] == null && $pointRange[1] != null) {
				if ($compValue <= $pointRange[1]) {
					return $points;
				}
			} elseif ($pointRange[0] != null && $pointRange[1] == null) {
				if ($compValue >= $pointRange[0]) {
					return $points;
				}
			} else {
				if ($compValue >= $pointRange[0] && $compValue <= $pointRange[1]) {
					return $points;
				}
			}
		}

		return null;
	}

	static function getBloodPressureTestData($isMale, $isTreated) {
		if ($isMale) {
			if ($isTreated) {
				return array( array(0, null, 119), array(1, 120, 129), array(2, 130, 139), array(2, 140, 159), array(3, 160, null));
			} else {
				return array( array(0, null, 119), array(0, 120, 129), array(1, 130, 139), array(1, 140, 159), array(2, 160, null));
			}
		} else {
			if ($isTreated) {
				return array( array(0, null, 119), array(3, 120, 129), array(4, 130, 139), array(5, 140, 159), array(6, 160, null));
			} else {
				return array( array(0, null, 119), array(1, 120, 129), array(2, 130, 139), array(3, 140, 159), array(4, 160, null));
			}
		}
	}

	static function getCHDRiskTestData($isMale) {
		if ($isMale) {
			return array( array("<1%", null, -1), array("1%", 0, 4), array("2%", 5, 6), array("3%", 7, 7), array("4%", 8, 8), array("5%", 9, 9), array("6%", 10, 10), array("8%", 11, 11), array("10%", 12, 12), array("12%", 13, 13), array("16%", 14, 14), array("20%", 15, 15), array("25%", 16, 16), array("30%", 17, null));
		} else {
			return array( array("<1%", null, 8), array("1%", 9, 12), array("2%", 13, 14), array("3%", 15, 15), array("4%", 16, 16), array("5%", 17, 17), array("6%", 18, 18), array("8%", 19, 19), array("11%", 20, 20), array("12%", 21, 21), array("17%", 22, 22), array("22%", 23, 23), array("27%", 24, 24), array("30%", 25, 25));
		}
	}

	static function calcFramScore($isMale, $age, $totalChol, $isCigSmoker, $hdlChol, $isTreated, $systBloodPressure) {
		if ($age>79) {
                    return array("N/A","N/A");
                }
                #age test
		$ageTest = self::getAgeTestData($isMale);
		$totalPoints = self::calcTestPointScore($age, $ageTest, true);

		#chol test
		$tcTest = self::getTotalCholTestData($isMale);
		foreach ($tcTest as $tcBlock) {
			list($startAge, $endAge, $tcAgeSpecific) = $tcBlock;
			if ($age >= $startAge && $age <= $endAge) {
				$totalPoints += intval(self::calcTestPointScore($totalChol, $tcAgeSpecific, true));
			}
		}

		#smoking test
		if ($isCigSmoker) {
			$totalPoints += intval(self::calcTestPointScore($age, self::getSmokerData($isMale), true));
		}

		$totalPoints += intval(self::calcTestPointScore($hdlChol, self::getHdlCholTestData($isMale), true));

		$totalPoints += intval(self::calcTestPointScore($systBloodPressure, self::getBloodPressureTestData($isMale, $isTreated)));

		$chdRiskIncrease = self::calcTestPointScore($totalPoints, self::getCHDRiskTestData($isMale));
		return array($totalPoints, $chdRiskIncrease);
	}

}
?>
