<?php

class ADD_Field_Container_EncounterModal extends ADD_Field_Container {
	
	private function determineRequiredFields($field,$form) {
		$required = array();
		if ($field->getSetting('required')) $required[]=array($field->getName(),$field->getTitle());
		foreach($form->getChildFields($field->getName()) as $childField) {
			$childRequired = $this->determineRequiredFields($childField,$form);
			foreach($childRequired as $cr) {
				$required[]=$cr;
			}
		}
		return $required;
	}
	function addMarkup($page) {
		$html = $page->html();
		$fname = $this->getName();
		$html->startElement("div",array("id"=>"modal-$fname","class"=>"modal hide","style"=>"width:800px"));
		$html->startElement("div",array("class"=>"modal-header"));
		$html->addTextElement("button","&times;",array("type"=>"button","class"=>"close","data-dismiss"=>"modal","aria-hidden"=>"true"));
		$html->addTextElement("h3","Modal header");
		$html->endElement("div");
		$html->startElement("div", array("class"=>"modal-body"));
		$html->addTextElement("div","",array("id"=>"modal-messages-$fname"));
		
		$form = $page->getComponent('Form');
		$childFields = $form->getChildFields($this);
		if ($childFields) {
			foreach($childFields as $childField) {
				if($childField->getSetting('disable_on_update')) {
					$html->startElement("div",array("class"=>"disable-on-update"));
				}
				$this->beforeAddChildHtml($childField,$page);
				$childField->createMarkup($page);
				$this->afterAddChildHtml($childField,$page);
				if($childField->getSetting('disable_on_update')) {
					$html->endElement("div");
				}
			}
		}
		
		$html->endElement("div");
		$html->startElement("div",array("class"=>"modal-footer"));
		$html->addTextElement("a","<i class='icon-remove'></i>&nbsp;Close",array("href"=>"javascript:;","onclick"=>"jQuery('#modal-$fname').modal('hide')","class"=>"btn","id"=>$this->getName()."_close_btn"));
		
		$required = $this->determineRequiredFields($this,$form);
		$options = array('beforeFunctionName'=>$this->getSetting('modal_beforejs'),'afterFunctionName'=>$this->getSetting('modal_afterjs'),'reqFields'=>$required,'elementId'=>$fname);
		$json = json_encode($options);
		$json = str_replace('"',"'",$json);
		$html->addTextElement("a","<i class='icon-ok icon-white'></i>&nbsp;Save changes",array("href"=>"javascript:;", "data-loading-text"=>"Please wait...","class"=>"btn btn-primary","id"=>$this->getName()."_save_btn"));
		$html->endElement("div");
		$html->endElement("div");
		$required = $this->determineRequiredFields($this,$form);
		$attrs = array("modal"=>$this->getName());
		$fields = array_map(function($value) { return $value[0]; },$required);
		$page->addJavascript("ADD.assign('ErrorRequired',".json_encode($fields).",".json_encode($attrs).");");
	}
}

?>