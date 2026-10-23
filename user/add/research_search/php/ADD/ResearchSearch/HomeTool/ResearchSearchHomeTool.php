<?php
namespace ADD\ResearchSearch\HomeTool;
use ADD\ResearchSearch\Util\ResearchSearchTable;
use ClinicLE\Client\Container\Cell\FieldCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\GotoPageFieldCell;
use ClinicLE\Client\Container\Container;
use ClinicLE\Client\Container\Table;
use ClinicLE\Options\HomeTool\AbstractPageSpecificHomeTool;

class ResearchSearchHomeTool extends AbstractPageSpecificHomeTool {
	public function createTool() {
	    $container = new Container("research-search","ResearchSearchController");
        $html = $container->getContent();
        $html->addTextElement("a","<i class='icon-list'></i>&nbsp;Study breakdown",array("class"=>"btn",
            "href"=>"/study/breakdown"));
        $html->addTextElement("a","<i class='icon-list'></i>&nbsp;Search study recruitments",array("class"=>"btn","href"=>"/study/search"));
		$html->addTextElement("label","OR");
		
		$html->startElement("div",array("id"=>"research-search","ng-controller"=>"ResearchSearchController","ng-cloak"=>"","cle-dataurl"=>"/api/research/single-search"));
		$html->startElement("div");
		$html->addTextElement("label","Search for a single research participant using:",array("class"=>"control-label"));
		$html->startElement("label",array("class"=>"radio inline"));
		$html->addHtml("ILH research ID (e.g. ILH4353453)");
		
		$html->addElement("input",array("value"=>1,"type"=>"radio","ng-model"=>"control.fields.search_type","name"=>"search_type"));
		$html->endElement("label");
		$html->startElement("label",array("class"=>"radio inline"));
		$html->addHtml("Study and study number");

		$html->addElement("input",array("value"=>2,"type"=>"radio","ng-model"=>"control.fields.search_type","name"=>"search_type"));
		$html->endElement("label");
		$html->endElement("div");

		$html->startElement("div",array("ng-show"=>"control.fields.search_type==1"));
		$html->addTextElement("label","Enter the ILH research ID");
		$html->addElement("input",array("type"=>"text","ng-model"=>"control.fields.research_id"));
		$html->endElement("div");

		$html->startElement("div",array("ng-show"=>"control.fields.search_type==2"));
		$option = new \ADD_Field_Option_Studies();
		
		$field = new \ADD_Field_List_Dropdown(array("Title"=>"Select the study:","Field"=>"study","Settings"=>array
        ("allStudies"=>1)));
		
		$option->initOptions($field, $this->getPage());
		$html->startElement("label");
		$html->addHtml("&nbsp;Select a study:<br/>");
		$html->endElement("label");
		
		$html->startElement("select",array("ng-model"=>"control.fields.study"));
		foreach($option->getOptionList() as $value => $label) {
			$html->addTextElement("option",$label,array("value"=>$value));
		}
		$html->endElement("select");
		$html->startElement("label");
		$html->addHtml("&nbsp;Enter a study number:<br/>");
		$html->addElement("input",array("type"=>"text","ng-model"=>"control.fields.study_no","class"=>"span2"));
		$html->endElement("label");	
		$html->endElement("div");
		
		$html->addTextElement("button","<i class='icon-search'></i>&nbsp;Search for participant",array
        ("class"=>"btn", "ng-show"=>"control.fields.search_type","ng-click"=>"search()"));
		
		$html->addHtml("<br/>");

        $rstable = new ResearchSearchTable();

        $rstable->createTable($html);

		return $html;
	}
	public function toolTitle() {
		return "Research Search";
	}
	public function toolWeight() {
		return 10;
	}
	public function isAvailable() {
		if(!\ADD::sys()->getAccessManager()->isLoggedIn()) return false;
		return $this->getPage()->getPermission('status_search');
	}
	public function toolClass() {}
}