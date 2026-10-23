<?php

class ADD_Field_EnrolSubmit extends ADD_Field_Checkbox implements ADD_Field_SubmitInterface {
	private $_submitted=false;
	public function addMarkup($page) {
		$html = ADD::web()->getContent();
		$value = $this->getValue();
		$enrolTitle = $this->getTitle();
			
		if($value!=1) {
			$html->startElement("div",array("class"=>"alert alert-info"));
			$html->addHtml("Referral not currently extended into a ".lcfirst($enrolTitle)."<br/><br/>");
			$submitContent = "<i class='icon-share icon-white'></i>&nbsp;Extend into ".lcfirst($enrolTitle);
			$html->addTextElement("button",$submitContent,array("name"=>$this->getName(),"value"=>"submit","type"=>"submit","class"=>"btn btn-primary"));
			$html->endElement("div");
			return;
		}
		$this->setTitle($enrolTitle."?");
		$this->setSetting('viewonly',1);
		parent::addMarkup($page);
	}
	
	public function beforeSubmitValues(&$values, $page) {
		if(!$this->isSubmittedField($values)) return;
		$values[$this->getName()]=1;
		ADD::web()->addMessage("Referral extended into ".lcfirst($this->getTitle()));
		$this->_submitted=true;
	}
	
	function isSubmittedField($values) {
		if (ADD_Util::val($this->getName(),$values)=="submit") {
			return true;
		}
	}
	
	public function submitValues($values,$page) {
	}

	public function afterSubmitValues($values, $page) {
		if(!$this->_submitted) return;
		$subjectUri = $page->getSiblingPage('subject')->createMenuUri();
		ADD::sys()->setState('redirect',$page->getSiblingPage('subject')->createMenuUri());
	}
}