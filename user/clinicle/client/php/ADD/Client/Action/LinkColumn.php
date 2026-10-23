<?php

class ADD_Client_Action_LinkColumn implements ADD_Client_Action_GridColumnActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return true;
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		
		if(ADD::sys()->hasState('readOnly') || ADD::sys()->hasState('locked')) {
			$html->startElement("span",array("ng-bind-html"=>"customCall('getLinkLabel',[grid[0][\$index],true])","class"=>"label label-success"));
			return $html;
		}
		
		
		$html->startElement("a",array("ng-bind-html"=>"customCall('getLinkLabel',[grid[0][\$index])])","ng-class"=>"\$index==0 ? 'hidden' : customCall('getLinkClass',[grid[0][\$index]])","ng-click"=>"customCall('toggleLink',[grid[0][\$index]])"));
		$html->addTextElement("span","");
		$html->endElement("a");
		return $html;
	}
	function getRowHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>