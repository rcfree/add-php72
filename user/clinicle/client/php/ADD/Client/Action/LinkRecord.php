<?php
use ClinicLE\Client\Util\AngularJS;
class ADD_Client_Action_LinkRecord implements ADD_Client_Action_GridRowActionInterface, ADD_Client_Action_GridFieldContainerInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		
		return true;
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		
		$ldfSetting = $this->getGridField()->getSetting('link_display_field');
		$linkDisplayField = "'".$ldfSetting."'" ? : "null";
		if(ADD::sys()->hasState('readOnly') || ADD::sys()->hasState('locked')) {
			$html->startElement("span",array("ng-bind-html"=>AngularJS::createCustomCall("getLinkLabel",array("row","true",$linkDisplayField)),"class"=>"label label-success"));
			return $html;
		}

		$html->startElement("a",array("ng-class"=>AngularJS::createCustomCall("getLinkClass",array("row.id")),"ng-click"=>AngularJS::createCustomCall("toggleLink",array("row.id","false",$linkDisplayField,"row"))));
		$html->addTextElement("span","",array("ng-bind-html"=>AngularJS::createCustomCall("getLinkLabel",array("row","false",$linkDisplayField))));
		$html->endElement("a");
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
	function getGridField() {
		return $this->gridField;
	}
	function setGridField($gridField) {
		$this->gridField = $gridField;
	}
	private $gridField;
}
?>