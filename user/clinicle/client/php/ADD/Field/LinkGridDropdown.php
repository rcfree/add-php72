<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_List_LinkGridDropdown extends ADD_Field_Base {
	function finaliseField($page) {
		$this->setSetting('empty_options',1);
		$this->setSetting('condition_test','""');
		$this->setSetting('condition_action','hide');
		$this->setSetting('condition_target',$this->assertSetting('linkgrid'));
	}
	function addMarkup($page) {
		$html = $page->html();
		$add = ADD::sys();
		$html->startElement("div",array("id"=>$this->getName(),"cle-value"=>$this->getValue(),"ng-controller"=>"EncounterGridController","cle-pim"=>$this->assertSetting('use_page'),"cle-pid"=>ADD::sys()->getPatientId()));

		$html->startElement("div",array("ng-hide"=>"loadingGrid==false"));
		$html->addHtml("Loading..&nbsp;");
		$html->addTextElement("img","",array("src"=>"/module/clinicle/client/images/ajax-loader.gif"));
		$html->endElement("div");
		
		$title = $page -> getSetting('title');
		$html->startElement("div",array("ng-hide"=>"loadingGrid==true"));
		$html->addTextElement("label",$this->getTitle());
		$attrs = array("ng-hide"=>"grid.length==0","ng-model"=>"parentid","ng-change"=>"updateList('".$this->assertSetting('linkgrid')."')","name"=>$this->getName(),"style"=>"width:".$this->getSetting('size',20)."em");
		if($add->hasState('locked') || $add->hasState('viewonly')) {
			$attrs['disabled']="disabled";
			$attrs['style']="border:none;background:none";
		}
		$html->startElement("select",$attrs);
		$html->addTextElement("option","-- choose --",array("value"=>""));
		$html->addTextElement("option",$this->getSetting('list_pattern'),array("ng-repeat"=>"row in grid","value"=>"{{ row.parent_encounter_id }}","ng-selected"=>"isSelected(row.parent_encounter_id)"));
		$html->endElement("select");
		$html->endElement("div");
		$html->endElement("div");
		$page->addJavascript("angular.bootstrap($('#".$this->getName()."'), ['addFramework']);");
	}
}