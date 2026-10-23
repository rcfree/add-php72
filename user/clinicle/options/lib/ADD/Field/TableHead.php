<?php

class ADD_Field_TableHead extends ADD_Field_Container {
	function startFieldHtml($page) {
		$html = $page->html();
		$html->startElement("thead");
		$html->startElement("tr");
	}
	function beforeAddChildHtml($childField,$page) {
		$html = $page->html();
		$html->startElement("th",array("class"=>"content-".$childField->getName()));
	}
	function afterAddChildHtml($childField,$page) {
		$html = $page->html();
		$html->endElement("th");
	}
	function endFieldHtml($page) {
		$html = $page->html();
		$html->endElement("tr");
		$html->endElement("thead");
		$html->startElement("tbody");
	}
}
?>