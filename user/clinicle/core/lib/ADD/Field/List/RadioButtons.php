<?php

class ADD_Field_List_RadioButtons extends ADD_Field_List {
	function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();
		$html->addTextElement("label",$this->getTitle(),array("class"=>"control-label"),$this->getName()."_label");
		$radioClass = "radio";
		if ($this->getSetting('inline')) $radioClass.=" inline";
		
		$html->startElement("div",array("style"=>"display:none"));
		$emptyAttrs = array("type"=>"radio","value"=>"","name"=>$this->getName());
		if(strlen($this->getValue())==0) $emptyAttrs['checked']='checked';
		$html->addElement("input",$emptyAttrs);
		$html->endElement("div");
		$ocount = 1;
		
		if (!$this->getOptions()) return;
        
		foreach($this->getOptions()->getOptionList() as $value => $label) {
			$attrs = array(
				"name" =>$this->getName(),
				"type" =>"radio",
				"value"=>$value,
				"autocomplete"=>"no",
				"view-disabled"=>"1"
			);
			if ($add->hasState('readOnly') || $this->getSetting('viewonly')) $attrs['disabled']='disabled';
			if ($this->getOptions()->countOptions()==$ocount) $radioClass=$radioClass." last";
			$html->startElement("label",array("class"=>$radioClass));
			if (strlen($this->getValue())>0 && $this->getValue() ==$value) $attrs['checked']="checked";
			$html->addElement("input",$attrs);
			if ($this->getSetting('show_value')) $html->addTextElement("span","{$value}&nbsp;&nbsp;&nbsp;&nbsp;",array("style"=>"float:left"));
			$html->addHtml($label."&nbsp;");
			if($add->config('show_option_values')) {
				$html->addTextElement("span",$value,array("font-weight:normal;font-size:10px","class"=>"badge badge-muted"));
			}
			$html->endElement("label");
			$ocount++;
		}
		
	}
	
	function updateExistingData($dbField) {
		if (!parent::updateExistingData($dbField)) return false;
        $edb = ADD::db('encounter');
		
		$obs = $edb->getObservationsUsingField($dbField);
		
		$newValues = array();
		foreach($obs as $ob) {
			$oldValue = $ob['value'];
			if (!is_array($oldValue)) {
				//$log->warn("No need to update ".$oldValue);
				continue;
			}
			if (count($oldValue)>1) {
				ADD::log($this)->warn("Field not changed into type RadioButtons");
				throw new ADD_FieldException("Cannot change encounter (".$ob['encounter_id'].") observation value (".$ob['id']."):".json_encode($oldValue)." into a RadioButtons value - it has multiple selections.");
			}
			
			foreach($oldValue as $key => $value) {
				$newValues[$ob['id']]=$value;
				ADD::log($this)->warn("Updated ".print_r($oldValue,1)." with '".$newValues[$ob['id']]."'");
			}
		}
		$this->saveChangedObservations($newValues,$obs);
		
		return true;
	}
	
}
?>