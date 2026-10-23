<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_TextReport extends ADD_Field_Base {

	function addMarkup($page) {
		$fieldName = $this->getName();
		$html = $page->html();
		$rmod = $page->getModule()->getSuperModule();
			
		ADD::sys()->setParam('name',$this->assertSetting('report'));
		ADD::sys()->setParam('module',$this->getSetting('module',$rmod->getAlias()));
		ADD::sys()->setParam('type',$this->getSetting('type','encounter'));
		
		if($this->getSetting('use_id')) {
			ADD::sys()->setParam('id',ADD::sys()->getParam('eid'));
		}
		else {
			ADD::sys()->setParam('id', null);
		}
		
		$reportPage = ADD::sys()->getPageInModule("report","report");
		
		$reportPage->setupComponents();
		$values=array();
		$content = $reportPage->createOutput($values);

		$html->addTextElement("label",$this->getTitle());
		$html->addTextElement("button","<i class='icon-share'></i>&nbsp;Copy to clipboard",array("type"=>"button","onclick"=>"copyToClipboard(field('$fieldName'))","class"=>"btn"));

		$html->addTextElement("pre",$content->output(),array("name"=>$fieldName,"class"=>"freetext-report","style"=>"height:200px;overflow-y:scroll"));
	}
}
