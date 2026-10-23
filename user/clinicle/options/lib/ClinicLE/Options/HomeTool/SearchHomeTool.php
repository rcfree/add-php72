<?php

namespace ClinicLE\Options\HomeTool;
use CLE;
use ClinicLE\Common\Util;

class SearchHomeTool extends AbstractHomeTool {
	
	public function isAvailable() {
		$add = CLE::sys();
		if($add->config('SearchHomeTool.disable')) return false;
		$pcpage = $add->getPageInModule('patient','new');
		$sppage = $add->getPageInModule('patient','search');

		$pccreate = $pcpage ? $pcpage->getPermission('create') : false;
		$spsearch = $sppage ? $sppage->getPermission('search') : false;
		
		if (!$pccreate && !$spsearch) {
			return false;
		}
		return true;
	}
	
	public function createTool() {
		$add = CLE::sys();
		$pid = $add->getPatientId();
		$pdb = $add->db('patient');
		$html = CLE::web()->newContent();
		
		$pcpage = $add->getPageInModule('patient','new');
		$sppage = $add->getPageInModule('patient','search');

		$pccreate = $pcpage ? $pcpage->getPermission('create') : false;
		$spsearch = $sppage ? $sppage->getPermission('search') : false;
		
		if (!$pccreate && !$spsearch) {
			$html->clear();
			return null;
		}
		if ($pid) {
				$html->addTextElement("p","To select a different patient you can:");
		}
		else {
				$add->setState('noSidebar',true);
				$html->addTextElement("p","Before you can add data you will need to select a patient. To do this you can:");
		}
		$selopts=0;
		if ($pccreate) {
				$html->addTextElement("a","<i class='icon-plus-sign icon-white'></i>&nbsp;Create new patient",array("class"=>"btn btn-primary","href"=>Util::uri("patient/new")));
				$html->addHtml("<span style='position:relative;top:3px'>&nbsp;in ".$add->getConfig()->getSetting('site_name','ADD')."</span>");
		}
		if ($spsearch) {
				if ($pccreate) $html->addTextElement("h5","OR");
				$html->startElement("form", array("class"=>"navbar-form","action"=>$sppage->createMenuPath(),"method"=>"post","onsubmit"=>"return false","ng-submit"=>"save(\$event)"));
				$html->addTextElement("label","Search for patients",array("class"=>"no-bullets"));
				$html->startElement("div",array("class"=>"input-append"));
				$html->addElement("input",array("name"=>"terms","class"=>"span4","type"=>"text"));
				$html->addTextElement("button","<i class='icon-search icon-white'></i>&nbsp;Search",array("class"=>"btn btn-primary"));
				$html->endElement("div");
				$html->addTextElement("span","Enter name(s), S number, NHS number or other identifier and click the 'Search' button",array("class"=>"help-block"));
				
				$html->endElement("form");
		}
		return $html;
	}
	public function toolTitle() {
		return "Select Patient";
	}
	public function toolWeight() {
		return 2;
	}
	public function toolClass() {}
}
?>
