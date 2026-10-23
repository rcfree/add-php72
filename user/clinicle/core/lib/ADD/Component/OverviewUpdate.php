<?php




class ADD_Component_OverviewUpdate extends ADD_Component {

    private $_overviewFileName = "overview.xml";
    private $updateFields = array();
    private $obs = array();
    
    private function reset() {
        $this->updateFields = array();
        $this->obs = array();
    }
   
    private function _determineTestResult($logic, $submitPage) {
        $submitName = $submitPage->getName();
        $mod = $submitPage->getModule();
        //loop through each 'input' element carrying out the test defined by the 'test' attribute
        $inputResult = true;
        $output = $logic->output;

        foreach ($logic->input as $input) {
            $sectionName = $input['section'];

            $inputPage = $mod->getPage("{$sectionName}");
            $test = isset($input['test']) ? $input['test'] : "get_obs";

            $inputTest = call_user_func_array("ADD_Tool_Logic::{$test}", array($input, $submitPage, $inputPage, $output));
            if (is_scalar($inputTest) && (!$inputTest || strlen($inputTest) == 0))
                $inputResult = false;
            $op = isset($input['op']) ? (string) $input['op'] : "eq";
            $value = isset($input['value']) ? $input['value'] : $inputTest;
            
            //test for a fail (e.g. when operator is ==, test for !=) inverse tests
            //if a single test fails then inputResult is set to false
            //if inputResult is true at the end of process, then all logic input tests are true	
            switch ($op) {
                case "eq":
                    if ($inputTest != $value)
                        $inputResult = false;
                    break;
                case "gt":
                    if ($inputTest <= $value)
                        $inputResult = false;
                    break;
                case "ge":
                    if ($inputTest < $value)
                        $inputResult = false;
                    break;
                case "lt":
                    if ($inputTest >= $value)
                        $inputResult = false;
                    break;
                case "any":
                    $value = $inputTest;
                    $inputResult = true;
                    break;
                default:
                    throw new ADD_PageException("Logic operator '$op' not recognised");
                    break;
            }
        }
        return array($inputResult,$value);
     }

    function getOverviewFileName() {
        return $this->_overviewFileName;
    }

    function setOverviewFileName($overviewFileName) {
        $this->_overviewFileName = $overviewFileName;
    }

    function afterSubmit(ADD_Page $submitPage) {
        $add = ADD::sys();
        $page = $this->_page;

        $this->reset();
        if ($submitPage->hasComponent('Patient'))
            return;
        if ($submitPage->hasComponent('Content')) {
            if (get_class($submitPage->getComponent('Content')) == "ADD_Component_Content_PrelimEntry")
                return;
        }
        $logicFile = $page->getModule()->getPath() . "/logic/" . $page->getName() . ".xml";
        if (!file_exists($logicFile)) {
            $submitPage->addWarning("Overview logic file '$logicFile' was not found");
            return;
        }
        if (!$page->isSetup())
            $page->setupComponents();

        $content = file_get_contents($logicFile);
        $xml = simplexml_load_string($content);

        $logics = $xml->logic;
        
        $relevantLogics = $this->extractRelevantLogics($logics,$submitPage);
        if (count($relevantLogics)==0) return;
        
        $er = null;

       try {
            $er = $page->getEncounterRecord();
       }
       catch(Exception $ex) {
           if (preg_match("/Supplied record .+ does not exist/",$ex->getMessage())) {
            $ppage = $page->getParentPage();
            $page->addWarning("Data fields in '".$page->getSetting('title')."' record were not updated as patient does not have an '".$ppage->getSetting('title')."' record");
            return;
           }
           else {
              throw $ex;
           }
       }
        $er->retrieve();
        $this->obs = $er->getObsValues();

        $prevLocked = $add->hasState('locked');

        $add->hasState('locked', false);

        foreach ($relevantLogics as $logic) {
            $this->findUpdatedFields($logic,$submitPage);
        }
        
        $changedFields = $this->populateChangedValues($submitPage);

        if ($changedFields) {
            $add->setState('locked', false);
            $er->audit("overview");
            $er->store($this->obs);
            
            foreach ($changedFields as $title => $displayText) {
                $submitPage->addMessage("Overview observation '{$title}' set to '{$displayText}'");
            }
        }
        if(!$page->hasComponent('RequiredFields') || !$this->getSetting('mark_complete')) {
        	$add->setState('locked', $prevLocked);
        	return;
		}
		$incompleteFields = $page->getComponent('RequiredFields')->findIncompleteFields($this->obs);

		$er = $page->getEncounterRecord();
        $er->retrieve();
        if($er->getEncData('is_invalid')) return;
        
        if (count($incompleteFields)>0) {
			$er->setEncData('is_complete',0);
			 $add->setState('complete',false);
		}
		else {
			$er->setEncData('is_complete',1);
			$add->setState('complete',true);
			$submitPage->addMessage("Overview record marked completed");
		}
		$er->store();
		$add->setState('locked', $prevLocked);
		
    }
    function extractRelevantLogics($logics, $submitPage) {
        $relevantLogics = array();
        $page = $this->_page;
        $submitModName = $submitPage->getModule()->getName();
        $submitPageName = $submitPage->getName();

        foreach($logics as $logic) {
            $input = $logic->input;
            $imod = ADD_Util::getArrayValue('mod',$input,$page->getModule()->getName());
            if ($imod!=$submitModName) {
                continue;
            }
            $ienc = $input->enc;

            $ignore = false;

            if ($ienc) {
                foreach($ienc as $enc) {
                    $iesect = ADD_Util::getArrayValue('section',$enc);
                    if ($iesect==$submitPageName) {
                        $ignore=false;
                        break;
                    }
                    else {
                        $ignore=true;
                    }
                }
            }
            else {
                $isect = ADD_Util::getArrayValue('section',$input);

                if ($isect!=$submitPageName) continue;
            }

            if (!$ignore) $relevantLogics[]=$logic;
        }
        return $relevantLogics;
    }

    function populateChangedValues($submitPage) {
        $changedFields = array();

        foreach ($this->updateFields as $name => $attrs) {

            list($action, $field, $value, $mainTitle) = $attrs;

            if ($action == 'add') {
                $changed = $field->addValue($value);
            } else {
                $changed = $field->removeValue($value);
            }

            if ($changed) {
                $this->obs[$name] = $field->getValue();
                $fieldTitle = $field->getTitle();

                if ($mainTitle) {
                    $changedFields[$mainTitle . " ($fieldTitle)"] = $field->getDisplayText($submitPage, null);
                } else {
                    $changedFields[$fieldTitle] = $field->getDisplayText($submitPage, null);
                }
            }
        }
        return $changedFields;
    }
    
    function findUpdatedFields($logic, $submitPage) {
        $db = ADD::db('encounter');
        $page = $this->_page;
        $subEncType = $page->getSetting('encounter');
        
        list($isPosResult, $resultValues) = $this->_determineTestResult($logic, $submitPage);
        $output = $logic->output;
        $action = (string) $output['action'];
        $outputTitle = (string) $output['title'];
        $override = ADD_Util::getArrayValue('override',$output);
        if ($override) {
            if (ADD_Util::getArrayValue($override,$this->obs)=="1") {
                $page->addMessage("'".$outputTitle."' was not updated as user values have been entered.");
                return;
            }
        }

        if ($resultValues) {
            if (!is_array($resultValues))
                $resultValues = array("{$output['obs']}" => $resultValues);
        }
        else {
            $resultValues = array("{$output['obs']}" => $resultValues);
        }
        $ff = new ADD_FieldFactory_Database();

        foreach ($resultValues as $obsName => $value) {
            $obsValue = isset($output["value"]) ? "{$output['value']}" : $value;

            $dbField = $db->getFieldFromEncounterForm($subEncType, $obsName);
            if (!$dbField) {
                $page->addWarning("There was no '$obsName' field defined in '$subEncType'");
                return;
            }
            $field = $ff->newInstance($dbField);
            $field->finaliseField($submitPage);
            $field->populateValue($this->obs, $submitPage);
            $changed = null;
            if ($action == 'toggle') {
                $obsValue = $isPosResult ? (string) $output['onValue'] : (string) $output['offValue'];
                $isPosResult = true;
            }
            $action = null;
            
            
            if ($isPosResult || $obsName != (string) $output['obs']) {
                $action = 'add';
            } else {
                if (isset($this->updateFields[$obsName]) && $this->updateFields[$obsName][0] == 'add') {
                    continue;
                }
                $action = 'remove';
            }
             
            if ($action)
                $this->updateFields[$obsName] = array($action, $field, $obsValue, $outputTitle);
        }
    }

}
