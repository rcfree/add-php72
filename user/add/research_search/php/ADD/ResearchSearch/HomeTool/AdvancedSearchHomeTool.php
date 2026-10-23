<?php
namespace ADD\ResearchSearch\HomeTool;

use ClinicLE\Options\HomeTool\AbstractPageSpecificHomeTool;

class AdvancedSearchHomeTool extends AbstractPageSpecificHomeTool {
	public function createTool() {
		$html = \ADD::web()->newContent();
		$html->addTextElement("p","You can use the i2b2 search tool to do an in-depth search of patient data.");
		$html->addTextElement("p","You will need to request login access to this tool via the administrator."); 
		$html->startElement("div");
		$html->addTextElement("span","Open i2b2 advanced search:");
		$html->addTextElement("a","<i class='icon-search icon-white'></i>&nbsp;Within ADD page",array("class"=>"btn btn-info","href"=>"/research/advanced-search"));
		$html->addHtml("&nbsp;");
		$html->addTextElement("a","<i class='icon-search icon-white'></i>&nbsp;As separate tab",array("class"=>"btn btn-success","href"=>\ADD::sys()->getConfig()->assertSetting("i2b2_url_path"),"target"=>"_new"));
		$html->endElement("div");
		return $html;
	}
	public function toolTitle() {
		return "Advanced Search";
	}
	public function toolWeight() {
		return 12;
	}
	public function isAvailable() {
		if(!\ADD::sys()->getAccessManager()->isLoggedIn()) return false;
		return $this->getPage()->getPermission('advanced_search');
	}
	public function toolClass() {}
}