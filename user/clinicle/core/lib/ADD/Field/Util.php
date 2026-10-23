<?php
use ClinicLE\System\Service\CoreServices;
class ADD_Field_Util {
	public static $values;
	
	static function __callStatic ($name,$arguments=array()) {
		if(method_exists("ADD_Field_Util",$name)) {
			return call_user_func_array("ADD_Field_Util::$name", $arguments);
		}
		$fieldFunctions = CLE::sys()->getService(CoreServices::FIELD_FUNCTIONS);
		return $fieldFunctions->callFunction($name,$arguments);
	}
    static function hasFunction($functionName) {
		if(method_exists("ADD_Field_Util",$functionName)) {
			return true;
		}
		$fieldFunctions = CLE::sys()->getService(CoreServices::FIELD_FUNCTIONS);
		return $fieldFunctions->hasFunction($functionName);
	}
	static function today() {
		return date("d/m/Y");
	}
	static function now() {
		return date("d/m/Y H:i");
	}
	static function timestamp($ts=null) {
	    if(!$ts) {
	        $ts = time();
        }
		return date("dmYHis",$ts);
	}
	static function pow($base,$exp) {
		return pow($base,$exp);
	}
	static function number_format($number,$dp) {
		return number_format($number,$dp);
	}
	static function age($startDate,$endDate=null) {
		$endDate = DateTime::createFromFormat('d/m/Y',$endDate);
		$startDate = DateTime::createFromFormat('d/m/Y',$startDate);
		if (!$endDate) return null;
                if (!$startDate) return null;
		$years = $endDate->format('Y') - $startDate->format('Y');
		//echo "month:".$startDate->format('m')." - ". $endDate->format('m')."\n";
	    if ($endDate->format('m') < $startDate->format('m') || 
	        $endDate->format('m') == $startDate->format('m') && $endDate->diff($startDate)->d>0) {
	        $years--;
	    }
		//echo "years:$years\n";
    	return $years;
	}
	static function date_of_birth() {
		$patId = ADD::sys()->getPatientId();
		if(!$patId) return null;
		$patient = ADD::db('patient')->getPatient($patId);
		if(!$patient) return null;
		return $patient['date_of_birth'];
	}
	static function current_user() {
		$add = ADD::sys();
		return $add->getAccessManager()->getUser()->username;
	}
	static function get($fieldName) {
        $temp = explode(":",$fieldName);
		if(count($temp)==2) {
			$accessManager = ADD::sys()->getAccessManager();
			$currentPage = $accessManager->getCurrentPage();
			
			$siblingObs = ADD_Field_Util::getSiblingObs($currentPage,$temp[0],array($temp[1])," update fields on this page that depend on it.");
			return $siblingObs[0];
		}
		if (!isset(self::$values[$fieldName])) return null;
        
		if (ADD_Util::isEmpty(self::$values[$fieldName])) return null;
        return self::$values[$fieldName];
    }
	static function getif($fieldName,$op,$ifValue,$trueValue,$falseValue) {
		$value = self::get($fieldName);
			
		if ($op=="eq") {
			return $value==$ifValue ? $trueValue : $falseValue;
		}
		if($op=="ge") {
			return $value>=$ifValue ? $trueValue : $falseValue;
		}
	}
	static function sum($fieldList) {
		$fields = explode(";",$fieldList);
		$sum = 0;
		foreach($fields as $field) {
			$fieldVal = self::get($field);
			if($fieldVal==null) return null;
			$sum+=$fieldVal;
		}
		return $sum;
	}
	
	static function getSiblingObs($page, $childName, $obsNames, $message,$values=array(), $ignoreMissing=false) {
            $add = ADD::sys();
			$parent = $page->getParentPage();
            $idValue = $add->getParam('eid');
			if(!$idValue) {
				$idValue = $add->getParam('parentid');
			}
			if(!$parent) {
				ADD::web()->addWarning("Parent of '".$page->getName()."' was not found");
				return;
			}
            $cpage = $parent->getChildPage($childName);
            if (!$cpage) {
                    $page->addWarning("Child page '$childName' was not found");
                    return;
            }
            
            $useValues=false;
			
            if ($page->getName()==$childName) {
				$form = $page->getComponent('Form');
				$form->setup();
                $useValues=true;
            }
            else {
				$cer = $cpage->getEncounterRecord($idValue);
				if(!$cer) {
					ADD::web()->addWarning("Record for '".$childName."' page does not exist");
					return;
				}
				$cer->retrieve();
				$form = $cpage->getComponent('Form');
				$form->setup();
            }
			
		$outputObs = array();
		$missingObs = array();
			foreach($obsNames as $obsName) {
				$obsValue = $useValues ? ADD_Util::val($obsName,$values) : $cer->getObsValue($obsName);
                if(ADD_Util::isEmpty($obsValue)) {
                    $of = $form->getField($obsName);
					
                    if (!$of) throw new ADD_FieldException("Field '$obsName' not found in page '".$cpage->getName()."'");
                    $missingObs[]=$of->getTitle();
                }
                else {
                    $outputObs[]=$obsValue;
				}
				
            }
            if (count($missingObs)>0 && !$ignoreMissing) {
                    $page->addWarning("Please enter '".implode("','",$missingObs)."' in ".($cpage == $page ? "this" : "'".$cpage->getSetting('title')."'")." page to ".$message);
                    return null;
            } 
			
            return $outputObs;
	}
        static function calculateExpression($field) {
            $exp = $field->getSetting('expression');
            if (preg_match("/toFixed\((.+)\)/", $exp, $matches)) {
                $exp = preg_replace("/\.toFixed\(.+\)/", "", $exp);
                $field->setSetting('fixed', $matches[1]);
            }
            
            $calc = ADD_Util::evaluate($exp);
			
            if ($calc!=null) {
                $fix = $field->getSetting('fixed');
				if ($fix) {
                    $round = ADD_Util::roundNumber($calc, $fix);
					$calc = $round;
				}
            }
            return $calc;
        }
		
}
