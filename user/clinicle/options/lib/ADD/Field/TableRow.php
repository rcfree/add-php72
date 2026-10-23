<?php
class ADD_Field_TableRow extends ADD_Field_Container {
	function startFieldHtml($page) {
		$html = $page->html();
		$html->startElement("tr");
	}
	function beforeAddChildHtml($childField,$page) {
		$html = $page->html();
		$html->startElement("td",array("class"=>"content-".$childField->getName()));
	}
	function afterAddChildHtml($childField,$page) {
		$html = $page->html();
		$html->endElement("td");
	}
	function endFieldHtml($page) {
		$html = $page->html();
		$html->endElement("tr");
	}
}
?>