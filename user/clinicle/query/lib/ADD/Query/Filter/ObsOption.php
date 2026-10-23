<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;

date_default_timezone_set("GB");
class ADD_Query_Filter_ObsOption extends ADD_Query_Filter_ObsValue {
	function beforeFilter(ADD_Query_Result $result, ADD_Query_Run $run) {
		$expValues = $this->_expValues;
		if(!is_array($expValues)) $expValues=array($expValues);
		$el = $result->getEncounterLookup();
		$page = $el->lookupPage($this->_encType);
		$form = $page->getComponent('Form');
		$form->setup();
		
		$field = $form->getField($this->getAttr("field"));
		$expLabels = array();
		foreach($expValues as $expValue) {
			$label = $field->getOptions()->getValueWithLabel($expValue);
			if(!$label) {
				throw new Exception("Option label '".$expValue."' is not in field '".$this->getAttr("field")."' of encounter type '".$this->_encType."'");
			}
			$expLabels[]=$label;
		}
		$this->_expValues=count($expLabels)==1 ? $expLabels[0] : $expLabels;
	}
}