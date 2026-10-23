<?php
class ADD_Field_Table extends ADD_Field_Container {
	function startFieldHtml($page) {
		parent::startFieldHtml($page);
		$html = $page->html();
		$class = $this->getSetting('class',"table table-condensed table-striped");
		$html->startElement("table",array("class"=>$class));
        if($this->getSetting("caption")) {
            $html->addTextElement("caption",$this->getSetting("caption"),array("class"=>"table-caption"));
        }
	}
	function endFieldHtml($page) {
		$html = $page->html();
		$html->endElement("tbody");
		$html->endElement("table");
		parent::endFieldHtml($page);
	}
}
?>