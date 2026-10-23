<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_LinkParentRecord extends ADD_Field_Hidden {
	function addMarkup($page) {
		$recordValue = $this->getValue();
		
		$linkClass = $recordValue ?  "" : "hidden";
		$unlinkClass = $recordValue ? "hidden" : "";
		
		$page->html()->startElement("div",array("id"=>$this->getName()."-linked","class"=>$linkClass));
		$page->html()->addTextElement("p","<b>".$this->getTitle()." <span id='". $this->getName()."-linked-detail'></span>linked.</b><a class='btn btn-success btn-small'><i class='icon-star icon-white'></i>&nbsp;Unlink</a>",array("class"=>"text-success"));
		$page->html()->endElement("div");
		
		$page->html()->startElement("div",array("id"=>$this->getName()."-unlinked","class"=>$unlinkClass));
		$linkText = "<b>No ".$this->getTitle()." linked.</b>";
		if(!ADD::sys()->hasState('readonly') && !ADD::sys()->hasState('locked')) {
			$linkText .= "&nbsp;Click the 'Link' button to link the selected set with this record.";
		}
		$page->html()->addTextElement("p",$linkText,array("class"=>"text-error"));
		$page->html()->endElement("div");
		
		parent::addMarkup($page);
	}
}