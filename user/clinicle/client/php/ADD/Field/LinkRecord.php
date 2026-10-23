<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_LinkRecord extends ADD_Field_Hidden {
	function beforeAddMarkup($page) {
		/*	
		if(!$this->getSetting("populate_values")) {
			return;
		}
		 * 
		 */
		$this->setSetting("ajax-func","LinkData");
	}
	function addMarkup($page) {
		$recordValue = $this->getValue();
		
		$linkClass = $recordValue ?  "" : "hidden";
		$unlinkClass = $recordValue ? "hidden" : "";
		$linkedGrid = $this->getSetting('linked_grid');
		$page->html()->startElement("div",array("id"=>$this->getName()."-linked","class"=>$linkClass));
		$page->html()->startElement("p",array("class"=>"text-success"));
		$page->html()->addTextElement("b",$this->getTitle()." <span id='". $this->getName()."-linked-detail'></span>linked (<span id='".$this->getName()."-value'></span>).");
		if($this->getSetting('required')) {
			$page->html()->addHtml("<span class='required'>*</span>");
		}
		$page->html()->addHtml("&nbsp;");
		if(!ADD::sys()->hasState('readonly') && !ADD::sys()->hasState('locked')) {
			$page->html()->addTextElement("a","<i class='icon-star icon-white'></i>&nbsp;Unlink</a>",array("class"=>"btn btn-success btn-mini ignoredirty","onclick"=>"CLE.Client.removeLink('".$this->getName()."','$linkedGrid')"));
		}
		$page->html()->endElement("p");
		$page->html()->endElement("div");
		
		$page->html()->startElement("div",array("id"=>$this->getName()."-unlinked","class"=>$unlinkClass));
		$linkText = "<b>No ".$this->getTitle()." linked.</b>";
		if(!ADD::sys()->hasState('readonly') && !ADD::sys()->hasState('locked')) {
			$linkText .= "&nbsp;".$this->getSetting('unlinked_text',($this->getSetting("not_addable") ? "If" : "Add a new one OR if")." any records are available above use a <button class='btn btn-default btn-small' type='button'><i class='icon-star-empty'></i></button> button to link a result with this record.");
		}
		if($this->getSetting('required')) {
			$linkText.= "<span class='required'>*</span>";
		}
		$page->html()->addTextElement("p",$linkText,array("class"=>"text-error"));
		
		$page->html()->endElement("div");
		
		if(!ADD::sys()->hasState('readonly') && !ADD::sys()->hasState('locked')) {
			$page->html()->startElement("div",array("id"=>$this->getName()."-no-records","class"=>"hidden"));
		
			$page->html()->addTextElement("p",$this->getSetting("no_records_footer"),array("class"=>"text-info"));
			$page->html()->endElement("div");
			
		}
		ADD::web()->addJavascript("\$(document).ready(function() { CLE.Client.hideLinkRecord('".$this->getName()."','$linkedGrid') });");
		parent::addMarkup($page);
	}
}