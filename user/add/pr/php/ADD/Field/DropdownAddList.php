<?php

class ADD_Field_DropdownAddList extends ADD_Field_List_MultiSelect {
	function addMarkup($page) {
		$readOnly = ADD::sys()->hasState('readOnly');
		$html = ADD::web()->getContent();
		$html->addTextElement("style","ul.multi-list { font-size:120%; list-style-type:none; margin-left:0; border-left: 1px solid #cccccc; width:auto; padding:4px;}");
		$html->addTextElement("label",$this->getTitle());
		$html->startElement("div",array("style"=>"padding-bottom:8px"));
		$ddSettings = $this->getSettings();
		unset($ddSettings['required']);
		$dd = new ADD_Field_List_Dropdown(array("Field"=>"__".$this->getName()."_list","Settings"=>$ddSettings));
		$dd->finaliseField($page);
		
		if(!$readOnly) $dd->createMarkup($page);
		
		$valueList = $this->getValue();
		
		if(!$readOnly) $html->addTextElement("button","Add to list..",array("type"=>"button","class"=>"btn btn-mini","onclick"=>"ADD.call('".$this->getName()."','add')"));
		$html->endElement("div");
		$html->startElement("ul",array("class"=>"multi-list multi-".$this->getName()."-list"));
		$option = $dd->getOptions();
			
		if($valueList) {
			$html->addTextElement("div","No concurrent diagnoses",array("class"=>"empty-".$this->getName()."-list hidden"));
			foreach($valueList as $key => $value) {
				$html->startElement("li",array("id" => $this->getName()."-".$key));
				if(!$readOnly) $html->addHtml("<button type='button' class='btn btn-mini btn-warning'><i class='icon-remove'></i></button>&nbsp;");
				$html->addHtml($option->findLabelWithValue($key));
				$html->addElement("input",array("type"=>"hidden","name"=>$this->getName()."[".$key."]","value"=>$value));
				$html->endElement("li");
			}
		}
		else {
			$html->addTextElement("div","No concurrent diagnoses",array("class"=>"empty-".$this->getName()."-list"));
		}
		$html->endElement("ul");
	}
	
	function finaliseField($page) {
		parent::finaliseField($page);
		$this->addJsValidation("ListAdd",$page);
	}
	
}

?>