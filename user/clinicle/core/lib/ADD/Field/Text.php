<?php

class ADD_Field_Text extends ADD_Field_Base {
    function addLabelMarkup($page)
    {
        $html = $page->html();
        $labelClass="";
        if($this->getSetting('label_size')) {
            $labelClass=" label-".$this->getSetting('label_size');
        }
        !$this->isTitleHidden() and $html->addTextElement("label",$this->getTitle(),array("class"=>"control-label$labelClass"),$this->getName()."_label");
    }
    function addFieldMarkup($page)
    {
        $html = $page->html();
        $action = ADD::sys()->getParam("a");
        if($this->getSetting("readonly_on_edit") && $action=="edit") {
            $this->setReadOnly(true);
        }
        $prefix = $this->getSetting('prefix');
        $suffix = $this->getSetting('suffix');

        if ($prefix && !$suffix) {
            $html->startElement("div",array("class"=>"input-prepend"));
            $html->addTextElement("span",$this->getSetting('prefix'),array("class"=>"add-on"));
        }

        if (!$prefix && $suffix) {
            $html->startElement("div",array("class"=>"input-append"));
        }

        $opts = array(
            "type"=>$this->getInputType(),
            "name"=>$this->getName().($this->getIdValue() ? "[{$this->getIdValue()}]" : ""),
            "value"=>$this->getValue(),
            "maxlength"=>$this->getSetting('size'),
            "style"=>"width:".(($this->getSetting('size',30)/2)+1)."em",
            "id"=>$this->getIdValue() ? $this->getName()."-{$this->getIdValue()}" :"edit-".str_replace("_","-",$this->getName())
        );
        if (ADD::sys()->hasState('readOnly') || $this->getSetting('viewonly') || $this->isReadOnly()) $opts['readonly']='readonly';
        $this->populateAttrs($opts);
        $html->addElement("input",$opts);

        if (!$prefix && $suffix) {
            $html->addTextElement("span",$this->getSetting('suffix'),array("class"=>"add-on"));
            $html->endElement("div");
        }

        if ($prefix && !$suffix) {
            $html->endElement("div");
        }

        if ($this->getSetting('mask')) {
            $this->addJQuery($page,'mask',"'{$this->getSetting('mask')}'");
        }
    }

	function getInputType() {
		return "text";
	}
	public function populateAttrs(&$attrs) {
		if (!$this->getSetting('tooltip')) return;
		$tooltip = $this->getSetting('tooltip');
		$attrs['data-placement']="top";
		$attrs['data-original-title']="'".$this->getTitle()."' is $tooltip";
		$attrs['data-toggle']="tooltip";
		$attrs['data-container']="body";
		$attrs['data-html']="true";
	}
	public function afterAddMarkup($page) {
		if (!$this->getSetting('tooltip') && !is_a($this,"ADD_Field_Expression")) return;
		//$this->addJQuery($page,'tooltip',"");
	}
}
?>