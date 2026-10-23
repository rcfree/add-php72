<?php
class ADD_Tool_Logic {
	static function get_obs($input, $page, $inputPage) {
		$add = ADD::sys();
		$db = ADD::db('encounter');	
		
		$obs = array();
		$obsName = (string)$input['obs'];
		
		$inputEncComp = $inputPage->getComponent('Encounter');
		$ier = $inputPage->getEncounterRecord();
                
		if (!$ier->exists()) return null;
		$ier->retrieve();
		return $ier->getObsValue($obsName);
	}
	
	static function is_selected($input, $page, $section) {
		$value = ADD_Tool_Logic::get_obs($input,$page,$section);
		$expValue = (string) $input['value'];
		if (isset($value[$expValue])) return true;
		return false;
	}
	
	static function is_not_selected($input, $page, $section) {
		$value = ADD_Tool_Logic::get_obs($input,$page,$section);
		$expValue = (string) $input['value'];
		if (isset($value[$expValue])) return false;
		return true;
	}
	
	static function count_encs($input, $page) {
		$add = ADD::sys();
		
		$obs = array();
		$obsName = $input['obs'];
		
		$per = $page->getParentPage()->getEncounterRecord();
                
		if (!$per->exists()) {
			return null;
		}
		$per->retrieve();
		$count = $per->countChildren($encType);
                return intval($count);
	}
	
	static function get_encs($input, $page) {
		$encType = $page->getSetting('encounter');
		$pid = ADD::sys()->getPatientId();
		$db = ADD::db('encounter');
		
		//$obsName = $input['obs'];
		
		//$encType = $section->get('encounter');
		//$parentEnc = $db->getEncounterWithPatientAndType($pid,$parent->get('encounter'));
		
		//if (!$parentEnc) {
		//	return null;
		//}
		
		$encs = $db->getEncountersWithPatientAndType($pid,$encType);
		$data = array();
		foreach($encs as $enc) {
			$obs = $db->getObservationsInEncounter($enc);
			$data[]=array('enc'=>$enc,'obs'=>$obs);
		}
		return $data;
	}
	
	static function max($input,$page,$section) {
		$data = ADD_Tool_Logic::get_encs($input,$page,$section);
		$obsName = (string)$input['obs'];
		$maxValue = 0;
		foreach($data as $item) {
			$obs = $item['obs'];
			if (isset($obs[$obsName])) {
				if ($obs[$obsName]>$maxValue) $maxValue=$obs[$obsName];
			}
		}
		return $maxValue;
	}
	
	static function latest_obs($input,$page,$section) {
		$data = ADD_Tool_Logic::get_encs($input,$page,$section);
		$dateObsName = (string)$input['date-field'];
		$inputObsName = (string)$input['obs'];
		
		usort($data,function($a,$b) use($dateObsName, $inputObsName) {
			
			$date_a = DateTime::createFromFormat('d/m/Y',ADD_Util::val($dateObsName,$a['obs']));
			$date_b = DateTime::createFromFormat('d/m/Y',ADD_Util::val($dateObsName,$b['obs']));
			if(!$date_a && !$date_b) {
				return 0;
			}
			if(!$date_a && $date_b) {
				return 1;
			}
			if($date_a && !$date_b) {
				return -1;
			}
			return ($date_a > $date_b) ? -1 : 1;
		});
		//ADD::web()->addWarning("encs:><pre>".print_r($data,1)."</pre>");
		return ADD_Util::val($inputObsName,$data[0]['obs']);
	}
	
	static function age($input, $page, $section) {
		$testValue = ADD_Tool_Logic::get_obs($input,$page, $section);
		if (!$testValue) return null;
		$lo = $page->getLayout();
		$db = $lo->getDatabase();
		$patient = $db->getPatient($lo->getPatientId());
		$patientDob = DateTime::createFromFormat("d/m/Y",$patient->getDateOfBirth());
		$testDate = DateTime::createFromFormat("d/m/Y",$testValue);
		$interval = $patientDob->diff($testDate);
		return $interval->format('%Y');
	}
	
	static function max_obs($input, $page, $section, $output) {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		$parent = $page->getParentPage();
		
		$obs = array();
		$encs = $input->enc;
		
		$maxObs = 0;
		$maxEnc = null;
		
		$outputObsName = (string) $output['obs'];
		$tgtObsNames = explode(";",(string) $output['otherObs']);
		$mod = (string)$input['mod'] ? $add->getModule((string)$input['mod']) : $page->getModule();
		
		//loop through each 'enc' element in the XML
		foreach($encs as $enc) {
			$esectName = "{$enc['section']}";
			$esect = $mod->getPage($esectName);
			$obsName = (string) $enc['obs'];
			$srcObsNames = explode(";",(string) $enc['otherObs']);
			$encType = $esect->getSetting('encounter');
			//get encounters for specific 'enc'
			$cencs = $db->getEncountersWithPatientAndType($add->getPatientId(),$encType);
			if (count($cencs)==0) continue;
			
			//loop through child encounters and set value to max value
			foreach($cencs as $cenc) {
				$obs = $db->getObservationsInEncounter($cenc);
				
				$value = ADD_Util::getArrayValue($obsName,$obs);
				if ($value && $value>$maxObs) {
					$maxObs=$value;
					$maxEnc = array(
						$outputObsName=>$value,			
					);
					if (count($srcObsNames)>0) {
						for($count=0;$count<count($srcObsNames);$count++) {
							$sourceObsName = $srcObsNames[$count];
							$targetObsName = $tgtObsNames[$count];
							$maxEnc[$targetObsName]=ADD_Util::getArrayValue($sourceObsName,$obs);
						}
					}
				}
			}
		}
		return $maxEnc;
	}

	static function compare_obs($input, $page, $section, $output) {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		
		$obs = array();
		$encs = $input->enc;
		
		$maxObs = 0;
		$maxEnc = null;
		
		$outputObsName = (string) $output['obs'];
		$tgtObsNames = explode(";",(string) $output['otherObs']);
		$module = $page->getModule();
		
		//loop through each 'enc' element in the XML
		foreach($encs as $enc) {
			$esectName = "{$enc['section']}";
			$esect = $module->getSection($esectName);
			$obsName = (string) $enc['obs'];
			$srcObsNames = explode(";",(string) $enc['otherObs']);
			$encType = $esect->get('encounter');
			
			//get encounters for specific 'enc'
			$cencs = $db->getEncountersWithPatientAndType($lo->getPatientId(),$encType);
			if (count($cencs)==0) continue;
			//loop through child encounters and set value to max value
			foreach($cencs as $cenc) {
				$obs = $db->getObservationsInEncounter($cenc);
				
				$value = ADD_Util::getArrayValue($obsName,$obs);
				if ($value && $value>$maxObs) {
					$maxObs=$value;
					$maxEnc = array(
						$outputObsName=>$value,			
					);
					if (count($srcObsNames)>0) {
						for($count=0;$count<count($srcObsNames);$count++) {
							$sourceObsName = $srcObsNames[$count];
							$targetObsName = $tgtObsNames[$count];
							$maxEnc[$targetObsName]=ADD_Util::getArrayValue($sourceObsName,$obs);
						}
					}
				}
			}
		}
		return $maxEnc;
	}
}
