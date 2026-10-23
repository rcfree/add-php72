<?php

class ADD_Component_AdvancedSearch extends ADD_Component {
	public function menuPath(&$menuPath) {
		$menuPath="research/advanced-search";
	}
	public function title(&$title) {
		$title="Search using i2b2 <a class='btn btn-small' target='_new' href='".ADD::sys()->getConfig()->assertSetting("i2b2_url_path")."'>Open in separate tab..</a>";
	}
	public function generate() {
		$html = ADD::web()->getContent('sideCommon');
		$html->clear();
		$html = ADD::web()->getContent();
		$html->addTextElement("iframe","",array(
		"frameborder"=>"0",
		"scrolling"=>"yes", 
		"name"=>"i2b2",
		"id"=>"i2b2",
		"style"=>"width: 100%; height:800px",
		"src"=>ADD::sys()->getConfig()->assertSetting("i2b2_url_path")
		));
	}

	public function expectedPermissions(&$expPerms) {
		$expPerms[]="advanced_search";
	}
}

