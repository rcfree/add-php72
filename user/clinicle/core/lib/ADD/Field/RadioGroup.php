<?php

class ADD_Field_RadioGroup extends ADD_Field_Container {

	protected $stored = false;

	function ignoreContainerTest() {
		return true;
	}

	function __construct($fieldData) {
        parent::__construct($fieldData);
        $fieldData['Field']=$fieldData['Field']."_list";
        $this->listGroup = new ADD_Field_List($fieldData);
    }

    function finaliseField($page)
    {
        parent::finaliseField($page);
        $this->listGroup->finaliseField($page);
    }

    /*
    function finaliseField($page) {
        parent::finaliseField($page);

        $form = $page->getComponent('Form');
        $childFields = $form->getChildFields($this);
        foreach($childFields as $childField) {
        //if(!$parentField) throw new ADD_FieldException("No parent RadioGroup found for field '".$this->getName()."'");
            $childField->setOptions($this->getOptions());
            $childField->setFinalised(true);
        }
    }
     *
     */
	function addMarkup($page) {
		$add = ADD::sys();
		$form = $page->getComponent('Form');
		$html = $page->html();
		$childFields = $form->getChildFields($this);
		if (!$childFields) return;
		
		$html->startElement("table", array("class"=>"table table-striped table-condensed table-radiogroup"));
		$html->addTextElement("caption",$this->getTitle());
		$html->startElement("thead");
		$options = $this->getOptions();
		
		$html->startElement("tr");
		$html->addTextElement("th","");
		$html->addTextElement("th",$this->getSetting('title_heading'));
		foreach($options->getOptionList() as $key => $value) {
			$html->addTextElement("th",$value,array("style"=>"text-align:center;width:6em"));
		}
		$html->endElement("tr");
		$html->endElement("thead");
		
		$html->startElement("tbody");
		foreach($childFields as $childField) {
			$childField->createMarkup($page);
		}
		$html->endElement("tbody");
		$html->endElement("table");
	}

	function getOptions() {
	    return $this->listGroup->getOptions();
    }
}