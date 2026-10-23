<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConceptGroup
 *
 * @author rcf8
 */

class ADD_Field_ConceptGroup extends ADD_Field_Container {
	public function beforeInstall(ADD_Form_FieldContainerInterface $form) {
		$sourceForm = $this->findSourceForm();

        $this->addSourceFieldsToTarget($sourceForm,$form, true);
	}
	public function findSourceForm() {
        $conceptType = $this->assertSetting('concept_type');

        if(preg_match("/\:/",$conceptType)) {
            $conceptPage = ADD::sys()->getPageFromPim($conceptType);
        }
        else {
            $conceptMod = ADD::sys()->getModule('concept');
            $conceptPage = $conceptMod->getPage($conceptType);
        }

        if(!$conceptPage) throw new ADD_PageNotFoundException("Concept group type '".$this->getSetting('concept_type')."' is not defined (field '".$this->getName()."')");
        $sourceForm = $conceptPage->getComponent('Form');
        $sourceForm->setup();
        return $sourceForm;
	}
	public function addChildFields($parentField, $childField,$sourceForm, $targetForm, $deleteConceptField=false) {
		if($targetForm->getField($childField->getName())) {
			throw new ADD_FieldExistsException("Unable to create concept group field '".$childField->getName()."' a field with this name already exists in the form");
		}
        if($deleteConceptField) {
		    CLE::info("delete concept field child:".$childField->getName());
		    $childField->setLevel("DEL");
        }
		$targetForm->addChildField($parentField->getName(),$childField);
        $sourceChildFields = $sourceForm->getChildFields($childField);
        if(!$sourceChildFields) {
            return;
        }
		foreach($sourceChildFields as $gcField) {
			$this->addChildFields($childField,$gcField,$sourceForm,$targetForm, $deleteConceptField);
		}

	}
	public function addSourceFieldsToTarget($sourceForm, $targetForm, $isBeforeInstall=true) {
        if ($this->getSetting('concept_block')) {
            $fieldsToUse = explode(";",$this->getSetting('concept_block'));
        }
        else {
            $fieldsToUse = $sourceForm->getTopLevelFields();
        }
        $deleteConceptField = false;
        if($this->getLevel() == "DEL") {
            $deleteConceptField = true;
        }
        foreach($fieldsToUse as $conceptFieldName) {
            $conceptField = $sourceForm->getField($conceptFieldName);
            if(!$conceptField) throw new ADD_FieldDoesNotExistException("Concept block '$conceptFieldName' is not defined in '".$sourceForm->getPage()->getName()."' (field '".$this->getName()."')");

            $this->addChildFields($this,$conceptField, $sourceForm, $targetForm,$deleteConceptField);
            if($isBeforeInstall) {
                $conceptField->beforeInstall($targetForm);
            }
        }
        if($targetForm->getPage()->getPim()=="blness:rclinstat") {
            CLE::info(array_map(function($f) { return
                $f->getName()
                .":".$f->getLevel()
                ; },$targetForm->getFields()));
        }
	}
	public function markChildFieldsDeleted($parentField, $form) {
	    $childFields = $form->getChildFields($parentField);
	    if(count($childFields)==0) {
	        return;
        }
        foreach($childFields as $childField) {
            $childField->setLevel("DEL");
            $this->markChildFieldsDeleted($childField,$form);
        }
    }
	public function finaliseField($page)
    {
        if(!is_a($page->getComponent("Form"),"ADD_Component_Form_File")) {
            return;
        }
        $sourceForm = $this->findSourceForm();
        $targetForm = $page->getComponent("Form");
        $this->addSourceFieldsToTarget($sourceForm,$targetForm, false);
    }
}
