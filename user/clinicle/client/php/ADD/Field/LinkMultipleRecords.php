<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_LinkMultipleRecords extends ADD_Field_Container {
	private $colCount=0;
	function beforeAddMarkup($page) {
	    $html = $page->html();
        $linkedGrid = $this->getSetting("linked_grid");
        $matrixField = $page->getComponent('Form')->getField($linkedGrid);
        $readonly = false;
        
        if(ADD::sys()->hasState('readOnly') || ADD::sys()->hasState('locked')) {
            $readonly = true;
        }
        
        $html->startElement("div");
            
        if(!$readonly) {
    		$html->startElement("p",array("class"=>"muted"));
    		$html->addHtml("To link ".$this->getSetting('link_source_title','data')." to this ".$this->getSetting('link_target_title','record')." click ");
    		$html->addTextElement("button","<i class='icon-star-empty'></i>",array("type"=>"button","class"=>"btn btn-small btn-default"));
    		$html->addHtml("&nbsp;buttons in the above grid.");
    		$html->endElement("p");
        }
        $fieldMap = $matrixField->getSetting("fieldmap");
        if(!$matrixField->getSetting("ignore_fieldmap") && $fieldMap) {
            return;
        }

        $html->startElement("div",array("style"=>"display:block;padding-bottom:8px;"));
        if($readonly) {
            $html->addTextElement("button", "<i class='icon-eye-open'></i>&nbsp;View linked data", array("type" => "button", "id" => $this->getName() . "-toggle-button", "onclick" => "CLE.Client.toggleDataDisplay('" . $this->getName() . "')", "class" => "btn btn-default btn-small pull-right"));
        }
        else {
            $html->addTextElement("button", "<i class='icon-pencil'></i>&nbsp;Edit linked data", array("type" => "button", "id" => $this->getName() . "-toggle-button", "onclick" => "CLE.Client.toggleDataDisplay('" . $this->getName() . "')", "class" => "btn btn-default btn-small pull-right"));
        }
        $html->startElement("div",array("class"=>"hidden","id"=>$this->getName()."-data-toggle"));
		$html->addTextElement("label",$this->getTitle());
        
		$html->startElement("div",array("class"=>"field-indent"));
        
		$html->startElement("table",array("class"=>"table table-condensed table-striped table-data"));
		$html->startElement("thead");
		$html->startElement("tr");
		$html->addTextElement("th","Test");
		$html->addTextElement("th",$this->getSetting("xfield_title","Date/time"));
		$yfields = $this->getSetting("yfields");
		if($yfields) {
		    $yfields = explode(";",$yfields);
        }
        else {
		    $yfields = array("value");
        }
        $yfieldTitle = $this->getSetting("yfield_title");
		if($yfieldTitle) {
		    $yfieldTitle = explode(";",$yfieldTitle);
        }
        else {
		    $yfieldTitle = array_map(function($value) { return \ClinicLE\Common\Util::nameToLabel($value);}, $yfields);
        }
        foreach($yfieldTitle as $yfieldTitleItem) {
            $html->addTextElement("th", $yfieldTitleItem);
        }
		$html->endElement("tr");
		$html->endElement("thead");
		$html->startElement("tbody");
	}
	function beforeAddChildHtml($childField,$page) {
		$childField->setSetting('clear_option','x');
		//$childField->setSetting('viewonly',true);
		$childField->setTitleHidden(true);
		$html = $page->html();
		if($this->colCount==0) {
			$html->startElement("tr");
			$html->addTextElement("td",$childField->getSetting("link_title"));
		}
		$html->startElement("td");
		
	}
	function afterAddChildHtml($childField,$page) {
		$html = $page->html();
		$html->endElement("td");
        $yfields = $this->getSetting("yfields");
        if($yfields) {
            $yfields = explode(";",$yfields);
        }
        else {
            $yfields = array("value");
        }
		if($this->colCount==count($yfields)) {
			$html->endElement("tr");
		}
		$this->colCount++;
		if($this->colCount>count($yfields)) {
			$this->colCount=0;
		}
	}
	public function setupLinkedOptions($matrixField) {
        $linkedOptFieldName= $matrixField->getSetting("yfield");
        $usePage = $matrixField->getSetting("use_page");
	    $pim = CLE::sys()->getPageFromPim($usePage);

	    $form = $pim->getComponent("Form");
	    $form->setup();
        $linkedOptField = $form->getField($linkedOptFieldName);
        if(!is_a($linkedOptField,"ADD_Field_List")) {
            return;
        }
        $this->linkedOptions = $linkedOptField->getOptions();
    }
    private $linkedOptions;
    function getLinkedOptionLabel($value) {
	    if(!$this->linkedOptions) {
	        return $value;
        }
        return $this->linkedOptions->findLabelWithValue($value);
    }
	public function beforeInstall(ADD_Form_FieldContainerInterface $form) {
        CLE::info("beforeAddMarkup:" . get_class($form));

        $form->setup();

        $this->addFieldsToForm($form);
		CLE::sys()->getEvents()->addListener(\ClinicLE\Install\Event\InstallEvents::AFTER_SYNC,array($this,"onAfterSync"));
	}
	private function addFieldsToForm($form) {
        $linkedGrid = $this->getSetting("linked_grid");

        $matrixField = $form->getField($linkedGrid);

        if(!$matrixField) {
            throw new Exception("Linked grid '".$linkedGrid."' not found");
        }
        $this->setupLinkedOptions($matrixField);

        $filterValues = $matrixField->getSetting("filter_value");
        $filterValue = $filterValues[0];
        $fieldMap = $matrixField->getSetting("fieldmap");
        if(!$matrixField->getSetting("ignore_fieldmap") && $fieldMap) {
            return;
        }

        $factory = new ADD_FieldFactory_RowData();
        $xfieldType = $this->getSetting("xfield_type","date_time");
        $yfields = $this->getSetting("yfields");
        if(!$yfields) {
            $yfields = array("value");
        }
        else {
            $yfields = explode(";",$yfields);
        }
        $linkedRequiredList = $this->getSetting("linked_required");
        $linkedRequired = $linkedRequiredList ? explode(";",$linkedRequiredList): array();
        foreach(explode(";",$filterValue) as $code) {
            $linkTitle = $this->getLinkedOptionLabel($code);
            $origName = $this-> getFieldData('Rename');
            $codeName = ADD_Util::labelToName($code);
            $required = in_array($code,$linkedRequired);
            $dateField = $factory->newInstance(array("Title"=>$this->getTitle()." ".$codeName." title","Settings"=>array("link_title"=>$linkTitle,"required" => $required),"Field"=>$this->getName()."_date_".$codeName, "Type"=>$xfieldType));
            if($this->getLevel() == "DEL") {
                $dateField->setLevel("DEL");
            }

            if($origName) {
                $dateField->setFieldData('Rename', $origName . "_date_" . $codeName);
            }
            $form->addChildField($this->getName(),$dateField);
            foreach($yfields as $yfield) {
                $numberField = $factory->newInstance(array("Title" => $this->getTitle() . " " . $codeName . " value", "Field" => $this->getName()."_".$yfield . "_" . $codeName, "Required" => $required,"Type" => "number", "Settings" => array("allow_op" =>
                    true,"required" => $required)));

                if ($this->getLevel() == "DEL") {
                    $numberField->setLevel("DEL");
                }
                if ($origName) {
                    $numberField->setFieldData('Rename', $origName . "_".$yfield . "_".$codeName);
                }
                $form->addChildField($this->getName(),$numberField);
            }

        }
    }
    public function finaliseField($page)
    {
        if(!is_a($page->getComponent("Form"),"ADD_Component_Form_File")) {
            return;
        }
        $form = $page->getComponent("Form");
        $this->addFieldsToForm($form);
    }
	function onAfterSync(\ClinicLE\Install\Event\DataHandlerEvent $event) {
        $formSync= $event->getFormSync();

        $missingFieldNames = $formSync->checkMissingFields();
        $deleted = $formSync->getDeleted();
        foreach($missingFieldNames as $missingFieldName) {
            if(preg_match("/^".$this->getName()."_/",$missingFieldName)===false) {
                continue;
            }
            CLE::info("Missing field: $missingFieldName was in linked data");
            $missingField = $formSync->getObsField($missingFieldName);
            $deleted[$missingFieldName]=$missingField;
        }

        $formSync->setDeleted($deleted);
    }
	function afterAddMarkup($page) {
		$page->html()->endElement("tbody");
		$page->html()->endElement("table");
		$page->html()->endElement("div");
        $linkedGrid = $this->getSetting("linked_grid");
        $matrixField = $page->getComponent('Form')->getField($linkedGrid);
        $fieldMap = $matrixField->getSetting("fieldmap");
        if(!$matrixField->getSetting("ignore_fieldmap") && $fieldMap) {
            return;
        }

        $page->html()->endElement("div");
		$page->html()->endElement("div");
		$page->html()->endElement("div");
	}
	function ignoreContainerTest() {
		return true;
	}
}