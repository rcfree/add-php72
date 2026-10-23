<?php
class ADD_Field_PushButtons extends ADD_Field_List {
	function getNgClick($optionValue,$page) {
		return sprintf($this->getSetting('ng-click'),$optionValue);
	}
	function getOnClick($optionValue,$page) {
		$onclick=$this->getSetting('onclick',"ADD.Touch.switchPushButton('".$this->getName()."','".$optionValue."',this);");
		return $onclick;
	}
	function getClassName($optionValue, $page) {
		if($this->getValue()==$optionValue) return "btn btn-primary";
		return "btn";
	}
	
	function create($page) {
		$html = new ADD_Content_Html();
		//if($this->getOptions()->hasNoOptions()) return;
		if(ADD::sys()->hasState(ADD_System::READONLY) && $this->getSetting('hide_on_view')) return;
			
		$columns = $this->getSetting('columns',1);
		$height = $this->getSetting('button_height');
		$html->addTextElement("label",$this->getTitle(),array("class"=>"control-label"),$this->getName()."_label");
            
		if($columns>1) {
			$html->startElement("table",array("style"=>"width:100%;margin-bottom:16px;"));
		}
		$c=0;
		$r=0;
		$html->startElement("tr");
		$btnStyle = "width:100%;";
		
		if($height) $btnStyle.=";height:".$height."px";
		
		$btnAttrs = array("type"=>"button","style"=>$btnStyle);
		$width = number_format((100/$columns)-2,0);
		$tdAttrs = array("style"=>"width:".$width."%");
		
		foreach($this->getOptions()->getOptionList() as $value => $label) {
			$html->startElement("td",$tdAttrs);
			
			$btnAttrs['ng-click']=$this->getNgClick($value,$page);
			$btnAttrs['onclick']=$this->getOnClick($value,$page);
			$btnAttrs['class']=$this->getClassName($value,$page);
			$btnAttrs['title']=$label;
			$html->startElement("button",$btnAttrs);
			$html->startElement("span",array("class"=>"ellipsis"));
			$html->addHtml($label);
			$html->endElement("span");
			$html->endElement("button");
			
			$html->endElement("td");
			$c++;
			
			if($c>=$columns) {
				$html->endElement("tr");
				$html->startElement("tr");
				$r++;
				$c=0;
			}

		}
		$html->endElement("tr");	
		$html->endElement("table");
		$html->addElement("input",array("value"=>$this->getValue(),"name"=>$this->getName(),"type"=>"hidden"));	
		return $html;
	}
	function addMarkup($page) {
		$html = $page->html();
		$html->addHtml($this->create($page));
	}
}
?>