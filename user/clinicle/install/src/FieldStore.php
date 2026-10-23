<?php

namespace ClinicLE\Install;

use ADD;
use ADD_Util;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FormStore
 *
 * @author rcf8
 */
class FieldStore {
	function __construct(FormSync $formSync) {
		$this->_formSync = $formSync;
	}
	function storeFields() {
        $this->_createObservationFields();
        $this->_moveObservationFields();
        $this->_updateObservationFields();
        $this->_renameObservationFields();
        $this->_deleteObservationFields();
        $this->_purgeObservationFields();
    }

    function _createObservationFields() {
        $encounterForm = $this->_formSync->getEncForm();
        $add = ADD::sys();
        foreach ($this->_formSync->getCreated() as $createField) {
            $obsField = $this->_saveField($createField);
            $add->db('audit')->auditObservationField('create', $this->_formSync->getAudit(), $obsField, $createField->getFieldData());
        }
    }

    function _updateObservationFields() {
        $fs = $this->_formSync;
        $add = ADD::sys();
		
        foreach ($fs->getUpdated() as $updateField) {
            $obsField = $fs->getObsField($updateField->getName());
            $this->_saveField($updateField, $obsField);
            ADD::db('audit')->auditObservationField('update', $fs->getAudit(), $obsField, $updateField->getFieldData());
        }
    }
	
	function _moveObservationFields() {
        $fs = $this->_formSync;
        $add = ADD::sys();
        foreach ($fs->getMoved() as $movedField) {
        	$obsField = $fs->getObsField($movedField->getName());
            
			$this->_saveField($movedField, $obsField);
        }
    }

    function _renameObservationFields() {
        $fs = $this->_formSync;
        $add = ADD::sys();
        foreach ($fs->getRenamed() as $renameField) {
            $prevName = $renameField->getFieldData('Rename');
            $obsField = $fs->getObsField($prevName);
            $renameField->setName($renameField->getName());
            $this->_saveField($renameField, $obsField);
            ADD::db('audit')->auditObservationField('rename', $fs->getAudit(), $obsField, array('previous_name' => $prevName));
        }
    }

    function _deleteObservationFields() {
        $fs = $this->_formSync;
        $add = ADD::sys();
        foreach ($fs->getDeleted() as $deleteField) {
            	
            if(is_array($deleteField)) {
				$obsField = $deleteField;
			}
			else {	
	            $obsField = $fs->getObsField($deleteField->getName());
			}
           $obsField['is_deleted']=1;
            ADD::db('encounter')->deleteObsField($obsField);
            //$obsField -> save($this -> conn);
            ADD::db('audit')->auditObservationField('delete', $fs->getAudit(), $obsField, null);
        }
    }

    function _purgeObservationFields() {
        $add = ADD::sys();
        $edb = ADD::db('encounter');
        $fs = $fs = $this->_formSync;
        foreach ($fs->getPurged() as $purgeField) {
            $edb->purgeObsField($fs->getObsField($purgeField['name']));
        }
    }

    function shouldUpdateField($dbField, $field) {
        $log = ADD::log($this);

        $dbSettings = $dbField->getSettings();
        $newSettings = $field->getSettings();
        if (isset($dbSettings['dictionary'])) {
            if ($dbSettings['dictionary'] != $newSettings['dictionary']) {
                $log->warn("Dictionary is not the same - not updated");
                return false;
            }
        }

        return true;
    }

    function _saveField($field, $obsField = null) {
		$edb = ADD::db('encounter');
        $encounterForm = $this->_formSync->getEncForm();
        $name = $field->getName();
        if (strlen($name) > 50)
            throw new ADD_FieldException("Field name '$name' is too long (>50 chars)");
        if (strlen($field->getType()) > 30)
            throw new ADD_FieldException("Field type '$name' is too long (>30 chars)");
        $settings = $field->getSettings();
		
		$isDeleted = $field->getLevel()=="DEL" ? 1 : 0;
        $ofdata = array(
            'level' => $field->getLevel(), 
            'weight' => $field->getWeight(), 
            'name' => $field->getName(), 
            'type' => $field->getType(), 
            'title' => $field->getTitle(), 
            'default_value' => $field->getDefault(), 
            'settings' => $settings ? ADD_Util::serialize($settings) : null, 
			'options'=>$field->getFieldData('Options') ? ADD_Util::serialize($field->getFieldData('Options')) : null,
            "is_deleted"=>$isDeleted
        );
		
        $ofdata['encounter_form_id'] = $encounterForm['id'];

        if ($obsField) {
			$obsField = $edb->updateObservationField($obsField, $ofdata,"date_updated");
        } else {
            $obsField = $edb->insertObservationField($ofdata);
        }

        return $obsField;
	}
}
