<?php

class ADD_Field_Checkbox extends ADD_Field_Base {
	
	public function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();	
		$checkClass = "inline control-label checkbox";
		
		$cboxValues = $this->getValue();
		$attrs = array(
			"name" =>$this->getName(),
			"type" =>"checkbox",
			"value"=>"1",
			"autocomplete"=>"off",
			"class"=>"single-check"
		);
		//$html->addTextElement("label","&nbsp;",array("class"=>"control-label"));
		$html->startElement("label",array("class"=>$checkClass));
		$html->addHtml($this->getTitle());
		
		if ($this->getValue()=='1') $attrs['checked']="checked";
		if($add->hasState('readOnly') ||$this->getSetting('viewonly')) {
			$hiddenAttrs = $attrs;
			$hiddenAttrs["style"]="display:none";
			$html->addElement("input",$hiddenAttrs);
			$attrs['name']="__".$this->getName()."_cbx";
		}
		if ($add->hasState('readOnly') || $this->getSetting('viewonly')) $attrs['disabled']='disabled';
		$html->addElement("input",$attrs);
		$html->endElement("label");
		
	}
	public function cleanValue(&$values) {
		if (!isset($values[$this->getName()])) {
			return '0';
		}
		else {
			return $values[$this->getName()];
		}
	}
	public function getDisplayText($form,$id) {
		if ($this->getValue()=='1') {
			return "1";
		}
		else {
			return "0";
		}
	}
	
	function afterPopulateFields($page) {
		$this->_setHiddenFields($page);
	}
	
	function beforeSubmit(&$values, $page) {
		$this->populateValue($values,$page);
		$this->_setHiddenFields($page);
	}
    function createAngularCell() {
        $fieldText = "row.".$this->getName().".value";
        return "<i ng-if=\"".$fieldText." == '1'\" class='icon-ok'></i>";
    }

}

?>