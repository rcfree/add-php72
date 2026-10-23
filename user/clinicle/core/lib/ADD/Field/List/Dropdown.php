<?php

class ADD_Field_List_Dropdown extends ADD_Field_List {
        function finaliseField($page) {
            parent::finaliseField($page);
            $emptyOption = $this->getSetting('empty_option',"-select option-");
            if (!$emptyOption) return;
            $options = $emptyOption ? array(""=>$emptyOption) : array();
        
            $this->_options->prependOption("",$emptyOption);
        }
		function createHtml($html, $page,$isReadOnly=false) {
			$add = ADD::sys();
				   $labelAttrs=array();
					if($this->getSetting('label_size')) {
						$labelAttrs['class']="label-".$this->getSetting('label_size');
					}
			!$this->isTitleHidden() and $html->addTextElement("label",$this->getTitle(),$labelAttrs,$this->getName()."_label");
			$sattrs = array(
				"name"=>$this->getName().($this->getIdValue() ? "[{$this->getIdValue()}]" :""),
				"id"=>$this->getName().($this->getIdValue() ? "-{$this->getIdValue()}" :""),
				"size"=>1
			);
			$sstyle = "";
			if ($this->getSetting('size')) $sstyle.="width:".$this->getSetting('size')."em;";
			
			$options = $this->getOptions();
					if(!$options) {
						throw new Exception("No options");
					}
			
			if(!$this->getSetting('always_enabled')) {
				if ($isReadOnly || $this->getSetting('readonly')) {
					$sattrs['readonly']='readonly'; 
					$sstyle.="display:none;";
					foreach($options->getOptionList() as $value => $label) {
						if ((string)$value===(string)$this->getValue()) {
							if(strlen($value)==0) $label="None selected";
							$html->addTextElement("div",$label,array("class"=>"disabled-select"));
						}
					}
					$this->setSetting('clear_option','x');
				}
			}
			if(strlen($sstyle)>0) $sattrs['style']=$sstyle;
			
			$html->startElement("select",$sattrs);
			
			foreach($options->getOptionList() as $value => $label) {
				$oattrs = array("value"=>$value);

				if ((string)$value===(string)$this->getValue()) {
					$oattrs['selected']='selected';
				}
				if($options->isOptionDisabled($value)) {
					//$oattrs['disabled']='disabled';
					if($add->hasState('new')) continue;
				}
				$html->addTextElement("option",$label,$oattrs);
			}
			$html->endElement("select");
			return $html;
		}
	function addMarkup($page) {
		$html = $page->html();
		$readOnly = ADD::sys()->hasState('readOnly') || $this->getSetting('viewonly');
		
			
		$this->createHtml($html, $page,$readOnly);
	}
	
}
?>