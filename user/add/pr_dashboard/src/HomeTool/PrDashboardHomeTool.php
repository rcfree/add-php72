<?php

namespace ADD\PR\HomeTool;
use CLE;
use ADD_Content_Html;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Options\HomeTool\AbstractHomeTool;
use ClinicLE\Options\HomeTool\AbstractPageSpecificHomeTool;

class PrDashboardHomeTool extends AbstractPageSpecificHomeTool {
	public function createTool() {
		$html = new HtmlElement();
        $html->addTextElement("a","<i class='fa fa-search'></i>&nbsp;PR data dashboard",array("href"=>"/pr/dashboard",
            "class"=>"btn"));
        return $html;
	}
	public function toolTitle() {
		return "Pulmonary rehab dashboard";
	}
	public function toolWeight() {
		return 1;
	}
	public function isAvailable() {
		return $this->getPage()->getPermission("view");
	}
	public function toolClass() {}
}