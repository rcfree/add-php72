<?php

class ADD_Component_StudySearch extends ADD_Component {
	public function menuPath(&$menuPath) {
		$menuPath="study/search";
	}
	public function title(&$title) {
		$title="Search study recruitments";
	}
	public function isPaged() {
		return true;
	}
	public function generate() {
		$container = new \ClinicLE\Client\Container\Container("research-search","ResearchSearchController");

        $container->appendAttrs(array("cle-parentid"=>"",
            "cle-pid"=>"",
            "cle-refresh"=>"research-search",
            "cle-title"=>"",
            "cle-dataurl"=>"/api/research/multi-search"));

        $html = $container->getContent();

		$statusOption = new ADD_Field_Option_DictionaryItems();
		
		$statuses = new ADD_Field_List_Dropdown(array("Title"=>"Recruitment status:","Field"=>"current_status","Settings"=>array("dictionary"=>"rrdb_status")));
		
		$statusOption->initOptions($statuses, $this->_page);
		
		$html->startElement("label");
		$html->addHtml("&nbsp;Recruitment status:<br/>");
		$html->endElement("label");
		
		$html->startElement("select",array("ng-model"=>"control.fields.status"));
		$html->addTextElement("option","-- any status --",array("value"=>""));
		foreach($statusOption->getOptionList() as $value => $label) {
			$html->addTextElement("option",$label,array("value"=>$value));
		}
		$html->endElement("select");
		
		$option = new ADD_Field_Option_Studies();
		
		$field = new ADD_Field_List_Dropdown(array("Title"=>"Select a study:","Field"=>"study","Settings"=>array("allStudies"=>1,"empty_value"=>"any")));
		
		$option->initOptions($field, $this->_page);
		$html->startElement("label");
		$html->addHtml("&nbsp;Select a study:<br/>");
		$html->endElement("label");
		
		$html->startElement("select",array("ng-model"=>"control.fields.study"));
		foreach($option->getOptionList() as $value => $label) {
			$html->addTextElement("option",$label,array("value"=>$value));
		}
		$html->endElement("select");
		
		$statusOption = new ADD_Field_Option_DictionaryItems();
		
		$statuses = new ADD_Field_List_Dropdown(array("Title"=>"Study status:","Field"=>"current_status","Settings"=>array("dictionary"=>"studystatus")));
		
		$statusOption->initOptions($statuses, $this->_page);
		
		$html->startElement("label");
		$html->addHtml("&nbsp;Study status:<br/>");
		$html->endElement("label");
		
		$html->startElement("select",array("ng-model"=>"control.fields.study_status"));
		$html->addTextElement("option","-- any status --",array("value"=>""));
		foreach($statusOption->getOptionList() as $value => $label) {
			$html->addTextElement("option",$label,array("value"=>$value));
		}
		$html->endElement("select");
		
		
		$html->addHtml("<br/>");
		
		$html->addTextElement("button","<i class='icon-list'></i>&nbsp;Find participants",array("class"=>"btn", "type"=>"button","ng-click"=>"refresh()"));
		$rsTable = new \ADD\ResearchSearch\Util\ResearchSearchTable();
        $rsTable->createTable($html);

		ADD::web()->getContent()->addHtml($container->createHtml());
	}

	public function expectedPermissions(&$expPerms) {
		$expPerms[]="status_search";
	}
}

