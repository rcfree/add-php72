<?php

namespace ClinicLE\Install;

use ADD;

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of DataSync
 *
 * @author rcf8
 */


class DataHandler {
    private $_formSync = null;
    
    function __construct($formSync) {
        $this->_formSync = $formSync;
    }

    function findChangedObservationFields($updateAll = false) {
        $add = ADD::sys();
        $log = ADD::log($this);

        $renamed = array();
        $updated = array();
        $created = array();
        $deleted = array();
        $moved = array();
		
        $this->weight = 1;
        $form = $this->_formSync->getFormFile();
        
		foreach ($form->getFields() as $field) {
			$fc = new FieldChange($field, $this->_formSync);
            $rename = false;
            $field->setWeight($this->weight);
            $this->weight++;
            $fieldName = $field->getName();
           
            if ($fc->isRenamed()) {
                $log->info("RENAME FIELD from '".$field->getFieldData('Rename')."' to '" . $field->getName()."'");
                $renamed[$field->getFieldData('Rename')] = $field;
                continue;
            }
            if ($fc->isUndeleted()) {
                $log->info("UNDELETE FIELD '" . $field->getName()."'");
                $updated[$fieldName] = $field;
                continue;
            }
            if ($fc->alreadyDeleted()) {
                $log->warn("DELETE FIELD '" . $fieldName . "' marked as DELeted but does not exist. Not creating");
                continue;
            }
            if ($fc->isCreated()) {
                $log->debug("CREATE FIELD '" . $fieldName. "'");
                $created[$fieldName] = $field;
                continue;
            }
            if ($fc->isDeleted()) {
                if (!$fc->exists()) {
                    $log->error("DELETE FIELD '" . $this->_field -> getName() . "' cannot be deleted as it does not exist");
                    continue;
                }
                
                $deleted[$fieldName] = $field;
                continue;
            }
            
            $isContainer = is_a($field,"ADD_Field_Container");
            $childFields = $form->getChildFields($field);
            $childCount = $childFields ? count($childFields) : 0;
            
            if (!$field->ignoreContainerTest()) {
                if ($isContainer) {
                    if ($childCount==0) throw new \Exception("Field '".$field->getName()."' is a container but has no child fields:".print_r($field->getFieldData(),1).print_r($form->getFields(),1));
                }
                else {
                    if ($childCount>0) throw new \Exception("Field '".$field->getName()."' has child fields but is not a container-type field:".print_r($field->getFieldData(),1));
                }
            }
            
			$fc->confirmDependencies();
			
            if ($fc->isChanged()) {
                $updated[$fieldName] = $field;
                continue;
            }
			
            if ($fc->isMoved()) {
                $moved[$fieldName] = $field;
                continue;
            }
			
            //$fc->resetWeight();
            
        }
        $this->_formSync->setCreated($created);
        $this->_formSync->setUpdated($updated);
        $this->_formSync->setRenamed($renamed);
        $this->_formSync->setDeleted($deleted);
        $this->_formSync->setMoved($moved);
    }

    function populateFormSync($encType,$page) {
		$edb = ADD::db('encounter');
        if($page->hasComponent('Encounter')) {
	        $encComp = $page->getComponent('Encounter');
			if(is_a($encComp,"ADD_Component_Encounter_Domain")) {
				$this->persistDomainType($encType);
				return;
			}
			
			if(is_a($encComp,"ADD_Component_Encounter_Section") || is_a($encComp,"ADD_Component_Encounter_RepeatSection")) {
				$this->persistSectionType($encType);
				return;
			}
		}
		$encForm = $edb->getEncounterFormWithType($encType);
        $fs = $this->_formSync;
		
        if (!$encForm) {
            ADD::log()->info("Create new encounter form '$encType'");
            $this->createEncounterForm($encType);
            return;
        }
            
        $obsFieldsByName = $edb->getObservationFieldsByIdAndName($encForm, true);
        
        $fs->setEncForm($encForm);
        $fs->setObsFields($obsFieldsByName);
    }

    function createEncounterForm($encType) {
        $add = ADD::sys();
        $log = ADD::log($this);

        if (strlen($encType) > 128)
            throw new ADD_EncounterException("Encounter type is too long (>128 chars)");

        $efData = array(
            "type" => $encType
        );
        $efData['revision_no'] = 1;
        $encForm = ADD::db('encounter')->insert("encounter_form", $efData,array("date_created","date_updated"));
       

        $fs = $this->_formSync;
        $aef = ADD::db('audit')->auditEncounterForm('create', $encForm);
        $fs->setEncForm($encForm);
        $fs->setAudit($aef);
        $fs->setNew(true);
    }
	function persistDomainType($domainType) {
		$edb = ADD::db('encounter');
        $domain = $edb->fetchFirst("select * from domain_type where name=?",$domainType);
		if($domain) {
			ADD::log()->info("Existing domain type '$domainType' not created");
        
			return;
		}
		ADD::log()->info("Create new domain type '$domainType'");
            
		$domain = $edb->insert("domain_type",array("name"=>$domainType));
		ADD::db('audit')->createAudit(array(
			'record_id'=>$domain['id'],
			'record_type'=>"domain_type",
			'record_name'=>$domainType,
			'action'=>'create',
		));
	}
	function persistSectionType($sectionType) {
		$edb = ADD::db('encounter');
        
        $section = $edb->fetchFirst("select * from section_type where name=?",$sectionType);
		if($section) {
			ADD::log()->info("Existing section type '$sectionType' not created");
        
			return;
		}
		ADD::log()->info("Create new section type '$sectionType'");
            
		$section = $edb->insert("section_type",array("name"=>$sectionType));
		ADD::db('audit')->createAudit(array(
			'record_id'=>$section['id'],
			'record_type'=>"section_type",
			'record_name'=>$sectionType,
			'action'=>'create',
		));
	}
    function updateEncounterForm() {
        $add = ADD::sys();
        $log = ADD::log($this);
        $fs = $this->_formSync;
        $encForm = $fs->getEncForm();
        
        $aef = null;
        if ($fs->totalChanges() == 0) {
            $log->info("NO CHANGES to fields in encounter form '".$encForm['type']."'");
            return null;
        }
        if ($fs->isNew()) {
            $log->info("New form - no need to update");
            return;
        }
        $revNo = $encForm['revision_no'];
        $revNo++;
        $encForm = ADD::db('encounter')->update("encounter_form", $encForm, array("revision_no" => $revNo),"date_updated");
        
        $log->info("Update encounter form '".$encForm['type']."' to revision no:$revNo");
        $aef = ADD::db('audit')->auditEncounterForm('update', $encForm);
        $fs->setEncForm($encForm);
        $fs->setAudit($aef);
    }

    
}


