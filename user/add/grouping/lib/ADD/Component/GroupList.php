<?php

class ADD_Component_GroupList extends ADD_Component {
	
	public function generate() {
		$content = ADD::web()->getContent();
		$groups = ADD::db('group')->getAllGroups();
		$content->startElement("table",array("class"=>"table table-condensed table-striped"));
		$content->addTextElement("caption",count($groups)." ".ADD_Util::pluralize($groups,"group"));
		$content->startElement("thead");
		$content->startElement("tr");
		$content->addTextElement("th","Group name");
		$content->addTextElement("th","Description");
		$content->addTextElement("th","Date created");
		$content->endElement("tr");
		$content->endElement("thead");
		$content->startElement("tbody");
		foreach($groups as $group) {
			$content->startElement("tr");
			$content->startElement("td");
			$content->addTextElement("a",$group['name'],array("href"=>ADD_Util::uri("grouping/patients",array("id"=>$group['id']))));
			$content->endElement("td");
			$content->addTextElement("td",$group['description']);
			$content->addTextElement("td",$group['date_created']);
			$content->endElement("tr");
		}
		$content->endElement("tbody");
		$content->endElement("table");
	}
	public function title(&$title) {
        $title=$this->_page->getSetting('title');
    }
   
	function expectedPermissions(&$expPerms) {
		$expPerms[]="view";
	}
	function menuPath(&$menuPath) {
		$menuPath="grouping/list";
	}
	function menuParams(&$menuParams) {
	}
} 