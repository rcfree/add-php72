<?php

class ADD_Field_Item extends ADD_Field_Base {
	protected $drupalElementType='item';
	protected $stored = false;
	
	function addMarkup($page) {
		$html = $page->html();
		$html->addTextElement('label',$this->getTitle());
	}
	function createPdfElement($rptField,$page,$currentX,$currentY) {
		$labelWidth = $this->createPdfLabel($rptField,$page,$currentX,$currentY);	
		return array(0,0);
	}
}
?>