<?php

namespace ClinicLE\Install;

use ADD;

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of DataUpdate
 *
 * @author rcf8
 */
class DataUpdate {
    private $edb = null;
	private $_conditions=array();
    function __construct($formSync) {
		$this->_formSync = $formSync;
    }
    function updateExpressionData($alwaysUpdate=false) {
		$edb = ADD::db('encounter');
        $add = ADD::sys();
        $log = ADD::log();
		$add->setParam(ADD_Page::T, uniqid("", true));
        $formSync = $this->_formSync;
		$page = $formSync->getPage();
		
		$log->info("Update expression data for page:".$page->getName());
       	$page->setupComponents();
       
        $page->setPermissions(array('view' => true, 'edit' => true, 'create'=>true));
        $form = $formSync->getFormFile();
        $form->prepareChildrenAndTopLevelFields();
		$encType = $page->getSetting('encounter');
		$sql = "select e.id,e.parent_encounter_id,e.patient_id, e.is_complete, e.is_locked, e.encounter_form_id from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=?";
		$params = array($encType);
        
        $estmt = $edb->execute($sql,$params);
        $ppage = $page->getParentPage();
		if(!$ppage) return;
		
		$gppage = $ppage->getParentPage();
        
        $hasEid = false;
        if ($ppage->hasComponent('Encounter')) {
            if (get_class($ppage->getComponent('Encounter')) == "ADD_Component_Encounter_RepeatSection") {
                $hasEid = true;
            }
        }
		$rf = null;
		if ($page->hasComponent('Locking')) {
			$rf = $page->hasComponent('RequiredFields') ? $page->getComponent('RequiredFields') : null;
		}
		
		$page->getComponents()->add($form);
        
		$skipResol=array();
		foreach ($form->getFields() as $field) {
		
			list($conditionTests,$conditionTargets,$conditionActions,$conditionOps) = $this->getConditions($field);
			if(count($conditionActions)>1) {
				$skipResol[$field->getName()]=true;
				$log->warn("DATA SKIP RESOLUTION - conditional field '".$field->getName()."' has too many conditionals");
			}
		}
		
		$expressionCount=0;
		$form->setup();
		
		foreach ($form->getFields() as $field) {
                $field->finaliseField($page);
				if($field->getSetting('expression')) $expressionCount++;
		}
		if($expressionCount==0 && !$alwaysUpdate) return;
		
		$encounterCount=0;
		$changedCount = 0;
		$unchangedCount=0;
		$completedCount=0;
        while($enc = $estmt->fetch(PDO::FETCH_ASSOC)) {
             $page->getComponent('Encounter')->reset();
             $ppage->getComponent('Encounter')->reset();
             if($gppage) $gppage->getComponent('Encounter')->reset();
			 $add->setPatientId($enc['patient_id']);
            
			 if ($hasEid)
                $add->setParam(ADD_Page::EID, $enc['parent_encounter_id']);
			 
            $add->setParam(ADD_Page::T, ADD_Util::token());
            $currData = $edb->getObservationsInEncounter($enc);
			$page->setData($currData);            
			     
            ADD_Field_Util::$values = &$currData;
            
			$this->hasDataChanged=false;
			
			$enFields = $this->getEnabledFields($currData,$formSync);
			
			foreach ($form->getFields() as $field) {
                if(!isset($skipResol[$field->getName()])) $this->resolveConditionals($currData,$field,$formSync,$enFields);
				$this->populateData($currData,$field,$page,$enFields);
			}
			
			if ($this->hasDataChanged) {
				$add->setParam(ADD_Page::T, uniqid("", true));
				
				$added=array();
				$changed=array();
				$diff = json_encode($page->getData())." => ".json_encode($currData);
                $oldData = $page->getData();
                foreach($currData as $newName=>$newValue) {
                	if(!isset($oldData[$newName])) {
                		$added[]="field:$newName, value:$newValue";
						continue;
					}
					$oldValue = $oldData[$newName];
					if($newValue==$oldValue) continue;
					
					$changed[]="field:$newName, old:$oldValue, new:$newValue";
				}
               
				$currData["__pid"]=$enc['patient_id'];
                $currData["__id"]=$enc['id'];
				$currData["__action"]="edit";
				
				try {
					$page->submitForm($currData);
                	ADD::web()->afterSubmit($page,$currData);
					$log->info("DATA EXPRESSION CHANGED for patient ".$enc['patient_id']." added:".json_encode($added).", changed:".json_encode($changed));
				
					$changedCount++;
				}
				catch(Exception $ex) {
					if(!preg_match("/Supplied record .+ does not exist/",$ex->getMessage())) {
						throw $ex;
					}
					else {
						$log->warn("DATA NOT CHANGED - supplied record '{$enc['id']}' with type '$encType' does not have expected parent");
					}
					$unchangedCount++;
				}
            }
            else {
				if($rf) {
					$incompleteFields = $rf->findIncompleteFields($currData);
					
					if(count($incompleteFields)==0 && $enc['is_complete']!=1) {
						$edb->update("encounter",$enc,array("is_complete"=>1));
						$log->info("DATA UNCOMPLETED encounter (".$enc['id']." - ".$encType."), patient ".$add->getPatientId().") now complete");
						$completedCount++;
					}
				}
				$unchangedCount++;
                //$log->debug("Expression results NOT changed for encounter ".$enc['id']." and patient ".$add->getPatientId());
            }
			$encounterCount++;
			if($encounterCount%1000==0) $log->info("DATA ENCOUNTERS CHECKED:$encounterCount, changed:$changedCount, unchanged:$unchangedCount, completed:$completedCount");
        }
		$log->info("DATA TOTAL ENCOUNTERS CHECKED:$encounterCount, changed:$changedCount, unchanged:$unchangedCount, completed:$completedCount");
    }
	
	function getConditions($field) {
		if(isset($this->_conditions[$field->getName()])) return $this->_conditions[$field->getName()];
		if ($field->getLevel()=="DEL") return;
                
		$tests = ADD_Util::makeArray($field->getSetting('condition_test'));
		$targets = ADD_Util::makeArray($field->getSetting('condition_target'));
		
		$actions = ADD_Util::makeArray($field->getSetting('condition_action'));
		$ops = ADD_Util::makeArray($field->getSetting('condition_op'));
		
		$conditions = array($tests,$targets,$actions,$ops);
		$this->_conditions[$field->getName()] = $conditions;
		return $conditions;
	}
	
	function getEnabledFields($currData,$formSync) {
		$form = $formSync->getFormFile();
		$enabledFields = array();
        foreach($form->getFields() as $field) {
			list($conditionTests,$conditionTargets,$conditionActions,$conditionOps) = $this->getConditions($field);
			$obsValue = ADD_Util::val($field->getName(),$currData);
			
			$conditionCount=0;
			if(count($conditionActions)>0) {
				foreach($conditionActions as $conditionAction) {
					$conditionArray = explode(";",$conditionTests[$conditionCount]);
					$targetNames = explode(";",$conditionTargets[$conditionCount]);

					$conditionCount++;
					if ($conditionAction!="enable") continue;

					$expValue = ADD_Util::evaluate($conditionArray[0]);

					if($expValue!=$obsValue) continue;

					$enabledFields=array_merge($enabledFields,$targetNames);
				}
			}
		}
		return $enabledFields;
	}
	
	function resolveConditionals(&$currData, $field,$formSync,$enFields) {
		$form = $formSync->getFormFile();
        $log = ADD::log();     
		$add = ADD::sys();
		if ($field->getLevel()=="DEL") return;
                
		list($conditionTests,$conditionTargets,$conditionActions,$conditionOps) = $this->getConditions($field);
		
		$conditionCount = 0;
		$hiddenFields = array();
		
		foreach($conditionActions as $conditionAction) {
			if (!isset($conditionTargets[$conditionCount])) {
					throw new ADD_SettingNotDefinedException("Field '{$field->getName()}' setting condition_target[$conditionCount] not defined");
			}
			$conditionArray = explode(";",$conditionTests[$conditionCount]);
			$targetNames = explode(";",$conditionTargets[$conditionCount]);
			
			if ($conditionAction!="show") continue;
			
			$currentValue = ADD_Util::val($field->getName(),$currData);
			$numberOfTests = count($conditionArray);
			foreach($conditionArray as $conditionTest) {
				$evaluatedTestValue = ADD_Util::evaluate($conditionTest);
				if(is_a($field,"ADD_Field_List_Checkboxes") && in_array($evaluatedTestValue,$currentValue ? $currentValue : array())) {
					//$log->debug("CURRENT OK - conditional field '".$field->getName()."' -> pid:".$add->getPatientId().", value:".json_encode($currentValue).($numberOfTests > 1 ? " (from multiple possible values:".implode(";",$conditionArray).")" : ""));#
					return;
				}
				if($this->equals($evaluatedTestValue,$currentValue)) {
					//$log->debug("CURRENT OK - conditional field '".$field->getName()."' -> pid:".$add->getPatientId().", value:".json_encode($currentValue).($numberOfTests > 1 ? " (from multiple possible values:".implode(";",$conditionArray).")" : ""));#
					return;
				}
			}
			
			$op = ADD_Util::val($conditionCount,$conditionOps,"eq");
			
			$conditionCount++;
			
			if ($op!="eq") continue;
			
			$counter = 0;
			
			foreach($targetNames as $targetName) {
				$targetField = $form->getField($targetName);
				
				if(!$this->isPopulated($targetField,$form, $currData)) continue;
				if ($numberOfTests > 1) {
					$log->info("DATA NO RESOLUTION - condition field '".$field->getName()."' -> pid:".$add->getPatientId().", value:".$currentValue." has multiple possible values:".implode(";",$conditionArray).")");
					return;
				}
				if(is_a($field,"ADD_Field_List_Checkboxes")) {
					$cboxData = $currData[$field->getName()];	
					$cboxData[$evaluatedTestValue]=$evaluatedTestValue;
					$evaluatedTestValue=$cboxData;
				}
				if(!ADD_Util::isEmpty($currentValue) && $currentValue!=$evaluatedTestValue) {
					$log->info("DATA NO RESOLUTION - condition field '".$field->getName()."' -> pid:".$add->getPatientId().", value:".$currentValue." is not empty)");
					continue;
				}
				$currData[$field->getName()]=$evaluatedTestValue;
				
				$log->info("DATA RESOLVE OK - conditional field '".$field->getName()."' -> ".json_encode($currData).", pid:".$add->getPatientId().", value:".json_encode($currentValue)." changed to ".json_encode($evaluatedTestValue)." based on data in '".$targetName."' field");
				$this->hasDataChanged=true;
			}			
			
		}
	}
	function isPopulated($field,$form, $values) {
		$populated=false;
		$this->isFieldPopulated($field,$form,$values,$populated);
		return $populated;
	}
	function isFieldPopulated($field,$form,$values, &$populated) {
		$fieldVal = ADD_Util::val($field->getName(),$values);
		
		if(!ADD_Util::isEmpty($fieldVal)) {
			$populated=true;
		}
		if(!ADD_Util::isEmpty($field->getDefault())) {
			ADD::log()->info("DATA SKIP RESOLUTION - Field '".$field->getName()."' has default '".$field->getDefault()."' which autopopulates");
			
			$populated=false;
		}
		$cfields = $form->getChildFields($field);
		if($cfields) {
			foreach($form->getChildFields($field) as $childField) {
				if(!$childField) print_r($field);
				$this->isFieldPopulated($childField,$form,$values,$populated);
			}
		}
	}
	function populateData(&$currData,$field,$page, $enFields) {
		if ($field->getType() != "Expression") return;
		if ($field->getSetting('overridable')) return;
		
		$fname = $field->getName();
		$ajax = $field->getSetting('ajax-func');
		
		if ($ajax) {
			$ajaxComp = ADD::sys()->getPageInModule('ajax','ajax')->getComponent("AjaxExecute");
            
			$outputs = $ajaxComp->createAjaxFunction($page,$ajax,$currData);

			foreach ($outputs as $ajaxFieldName => $value) {
				$fix = $field->getSetting('fixed');
				if ($fix) {
                    $value = ADD_Util::roundNumber($value, $fix);
				}
				if ($this->equals($value,ADD_Util::val($ajaxFieldName,$currData))) continue;
				ADD::log()->debug("DATA RECALCULATED - ajax field '".$ajaxFieldName."'-> pid:".ADD::sys()->getPatientId().", current value: ".ADD_Util::val($ajaxFieldName,$currData).", new value: $value");
				$currData[$ajaxFieldName] = $value;
				$this->hasDataChanged=true;
			}
		}
		
		$exp = $field->getSetting('expression');
		if (!$exp) return;
		if(in_array($fname,$enFields)) {
			return;
		}
		$calc = ADD_Field_Util::calculateExpression($field);
		
		if (!$this->equals(ADD_Util::val($fname,$currData),$calc)) {
			ADD::log()->debug("DATA RECALCULATED - expression field '".$fname."'-> pid:".ADD::sys()->getPatientId().", current value: ".ADD_Util::val($fname,$currData).", new value: $calc");
					
			$currData[$fname] = $calc;
			$this->hasDataChanged=true;
		}
	}	
	function equals($value1,$value2) {
		if(strlen($value1)==0 && strlen($value2)==0) return true;
		
		if(is_float($value1) && is_float($value1)) {
			if(floatval($value1)===floatval($value2)) {
				return true;
			}
			return false;
		}
		
		if(is_numeric($value1) && is_numeric($value2)) {
			if(intval($value1)===intval($value2)) return true;
		}
		
		if(is_string($value1) && is_string($value2)) {
			if (strval($value1)===strval($value2)) return true;
		}
		
		return false;
	}
	
	function updateListObservationData() {
        $fs = $this->_formSync;
        $add = ADD::sys();
		
		$form = $this->_formSync->getFormFile();

		$fs->getPage()->getComponents()->add($form);
		$fields = $this->_formSync->getUpdated();
        foreach ($fields as $field) {
			if(!is_a($field,"ADD_Field_List")) continue;
			$obsField = $fs->getObsField($field->getName());
			if(!$obsField) continue;
            $field->finaliseField($fs->getPage());
            if(is_a($field,"ADD_Field_List") && !$field->getOptions()) {
            	$field->beforePopulateFields($fs->getPage());
			}
			
			$field->updateExistingData($obsField);
            
        }
    }
}
