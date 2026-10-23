<?php
use ClinicLE\Client\Util\AngularJS;

class ADD_Client_Action_LinkCell implements ADD_Client_Action_GridCellActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return true;
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->startElement("span",array("class"=>"link-cell"));
		if(ADD::sys()->hasState('readOnly') || ADD::sys()->hasState('locked')) {
		    $attrs = array("ng-bind-html"=>AngularJS::createCustomCall("getCellLinkLabel",array("cell","true")),"ng-class"=>AngularJS::createCustomCall("getCellLinkClass",array("cell","true")));
			$html->startElement("span",$attrs);
			return $html;
		}
		
		$attrs = array("ng-bind-html"=>AngularJS::createCustomCall("getCellLinkLabel",array("cell")),"ng-class"=>AngularJS::createCustomCall("getCellLinkClass",array("cell")),"ng-click"=>"cell.link_column ? ".AngularJS::createCustomCall("linkCellColumn",array('$index'))." : ".
            AngularJS::createCustomCall("toggleCellLink",array('cell')));
		$html->startElement("a",$attrs);
		$html->addTextElement("span","");
		$html->endElement("a");
        $html->endElement("span");
		return $html;
	}
	function getRowHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}

}
