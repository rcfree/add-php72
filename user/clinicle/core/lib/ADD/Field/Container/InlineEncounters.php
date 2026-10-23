<?php

class ADD_Field_Container_InlineEncounters extends ADD_Field_Container_MultiEncBase {
	
	function addMarkup($page) {
		$add = ADD::sys();
		$form = $page->getComponent('Form');
		$db = ADD::db('encounter');
		$html = $page->html();
		$childFields = $form->getChildFields($this);
		$html->startElement("div");
		$html->startElement("table",array("class"=>"table table-striped table-condensed","name"=>$this->getName()));
		$rowEncs = $this->getRowEncounters($page);
		$html->addTextElement("caption",count($rowEncs)." " . ADD_Util::pluralize($rowEncs,$page->getSetting('single')." record"));
		$reqFields = array();

 
        if (count($rowEncs)>0) {
		$html->startElement("thead");
		$html->startElement("tr");
		foreach($childFields as $childField) {
			$html->startElement("th");
			$html->addHtml($childField->getTitle());
			if ($childField->getSetting('required')) {
				$html->addTextElement("span","*",array("class"=>"required","style"=>"color:red"));
			}
			$childField->setTitleHidden(true);
		}
		$html->endElement("tr");
		$html->endElement("thead");
		$html->startElement("tbody");
		foreach($rowEncs as $encId => $er) {
                    
			$er->retrieve();
			$obs = $er->getObsValues();
			$result = array();
			
			$html->startElement("tr");
			
			foreach($childFields as $childField) {
				$childField->clearValue();
				$childField->populateValue($obs,$page,true);
				$childField->setIdValue($encId);
				$html->startElement("td");
				$childField->createMarkup($page);
				$html->endElement("td");
				if ($childField->getSetting('required')) {
					$fieldId = "#".$childField->getName()."-".$encId;
					$reqFields[]=array($fieldId,$childField->getTitle());
                    $childField->addJsValidation("ErrorRequired",$page);
					//$childField->addJQuery($page,"change","function() { errorRequired('$fieldId'); }");
				}
			}
			$html->startElement("td");
			$html->addElement("input",array("type"=>"hidden","name"=>"__record_id","value"=>$encId));
			$html->endElement("td");
			$html->endElement("tr");
		}
		$html->endElement("tbody");
		}
		$html->endElement("table");
		$html->endElement("div");
		
		//$page->addJavascript("window.requiredFields=[]","head");
		//$page->addJavascript("window.requiredFields=".json_encode($reqFields).";");

		
	}
}
?>