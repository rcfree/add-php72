<?php

class ADD_Field_RadioLabelsHoriz extends ADD_Field_List {
	protected $stored = false;
    function startFieldHtml($page)
    {
    }

	function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();
		$radioClass = "radio";
		$ocount = 1;

		foreach($this->getOptions()->getOptionList() as $value => $label) {
			$html->startElement("th");
			$html->addHtml($label);
			$html->endElement("th");

		}
	}

    function endFieldHtml($page)
    {
    }
}
?>