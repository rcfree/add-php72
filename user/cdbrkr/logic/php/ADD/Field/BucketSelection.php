<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_BucketSelection extends ADD_Field_List {
	public function isStored() {
		return false;
	}
	
	
	function beforeAddMarkup($page) {
		
		$ff = new ADD_FieldFactory_RowData();
		
		$this->areaField = $this->_createAreaButtons($ff,$page);
		
		$areas = array();
		
		foreach($this->areaField->getOptions()->getOptionList() as $value=>$title) {
			if(strtoupper($title)=="SEARCH") {
				continue;
			}
			$areas[]=$this->_createGroupButtons($ff,$value,$title,$page);
		}
		$this->areas = $areas;
		
		$this->optionsField = $this->_createOptionButtons($ff,$page);
	}
	
	function _createOptionButtons($ff,$page) {
		$level = $this->getLevel();
		$fieldName = $this->getName();
		
		$fieldData = $this->fieldData;
		$fieldData["Field"]=$fieldName."_matrix_set";
		$fieldData["Level"]=$level+1;
		$fieldData["Type"]="matrix_set";
		$fieldData["Title"]=$this->getSetting("option_title");
		
		$optionsField = $ff->newInstance($fieldData);
		$optionsField->finaliseField($page);
		$optionsField->setSetting("columns",$this->getSetting("option_columns",3));
		$optionsField->setSetting("parent_field",$this->getName());
		$optionsField->setSetting("cell_pattern",$this->getSetting("option_cell_pattern","cell.description"));
		$optionsField->setSetting("use_page",$this->assertSetting('check_select_page'));
		$optionsField->setSetting("hide_on_view",1);
		return $optionsField;
	}
	
	function addMarkup($page) {
		if(ADD::sys()->hasState(ADD_System::READONLY)) {
			$page->html()->addTextElement("p","Selection options are not available in view only mode",array("class"=>"text-info lead"));
			return;
		}
		$this->areaField->createMarkup($page);
		$page->html()->startElement("div",array("id"=>$this->getName()."_area_container"));
		foreach($this->areas as $area) {
			$area->createMarkup($page);
		}
		$page->html()->endElement("div");
		
		$this->optionsField->createMarkup($page);
	}
	
	function _createAreaButtons($ff,$page) {
		$level = $this->getLevel();
		$fieldName = $this->getName();
		
		$fieldData = $this->fieldData;
		$fieldData["Field"]=$fieldName."_areas";
		$fieldData["Level"]=$level+1;
		$fieldData["Type"]="area_push_buttons";
		
		$areaField = $ff->newInstance($fieldData);
		$areaField->setSetting('dictionary_option','SeparateTable');
		$areaField->setSetting('dictionary','icd10_area');
		$areaField->setSetting('dictionary_value_field','id');
		$areaField->finaliseField($page);
		$areaField->setSetting("columns",$this->getSetting("area_columns",5));
		$areaField->setSetting("ignore_content",1);
		$conditionAction=array();
		$conditionTest=array();
		$conditionTarget=array();
		foreach($areaField->getOptions()->getOptionList() as $value => $label) {
			if(strtoupper($label)=="SEARCH") {
				$conditionAction[]="hide";
				$conditionTest[]=$value;
				$conditionTarget[]=$fieldName."_matrix_set";
				$conditionAction[]="show";
				$conditionTest[]=$value;
				$conditionTarget[]=$fieldName."_search_group_buttons";
			}
			else {
				
				$conditionAction[]="show";
				$conditionTest[]=$value;
				$conditionTarget[]=$fieldName."_".strtolower($value)."_group_buttons";
			}
		}
		$areaField->setSetting("condition_action",$conditionAction);
		$areaField->setSetting("condition_test",$conditionTest);
		$areaField->setSetting("condition_target",$conditionTarget);
		$areaField->setSetting("parent_field",$this->getName());
		$areaField->setSetting("hide_on_view",1);
		$areaField->setSetting("button_height",$this->getSetting("area_height",28));
		return $areaField;
	}
	function _createGroupButtons($ff,$groupName,$groupTitle,$page) {
		$level = $this->getLevel();
		$fieldName = $this->getName();
		
		$fieldData = $this->fieldData;
		$fieldData["Field"]=$fieldName."_".strtolower($groupName)."_group_buttons";
		$fieldData["Title"]="Click a ".strtolower($this->assertSetting('group_info'))." button to show the available ".$this->assertSetting('option_info')." in the '".$groupTitle."' group";
		$fieldData["Level"]=$level;
		$fieldData["Type"]="group_push_buttons";
		$fieldData["Settings"]=array_merge($fieldData["Settings"],array(
			"columns"=>$this->getSetting("group_columns",3),
			"refresh_field"=>$fieldName."_set",
		'dictionary_option'=>'SeparateTable',
		'dictionary'=>'icd10_group',
		'dictionary_filter'=>'icd10_area_id',
		'dictionary_value'=>$groupName,
		'dictionary_order'=>'title'
		));
		
		
		$groupField = $ff->newInstance($fieldData);
		$groupField->finaliseField($page);
		
		$groupField->populateValue(array(),$page);
		$groupField->setSetting("parent_field",$this->getName());
		$groupField->setSetting("hide_on_view",1);
		$groupField->setSetting("button_height",$this->getSetting("group_height",28));
		return $groupField;
	}
}