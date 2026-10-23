<?php

class ADD_Field_Icon extends ADD_Field_Base {
	protected $stored = false;
	function addMarkup($page) {
		$html = $page->html();
		$html->addTextElement('i',"",$this->getSettings());
	}
}
?>