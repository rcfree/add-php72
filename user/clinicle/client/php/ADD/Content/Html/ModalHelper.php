<?php
class ADD_Content_Html_ModalHelper {
	private $_title;
	private $_id;
	private $_htmlAttrs = array();
	private $_buttons=array();
	private $_modalBody;
	
	function __construct($title, $id, $htmlAttrs=array(), $buttons=array()) {
		$this->_title = $title;
		$this->_id = $id;
		$this->_htmlAttrs = $htmlAttrs;	
		$this->_buttons = $buttons;
		$this->_modalBody = new ADD_Content_Html();
	}
	
	function getModalBody() {
		return $this->_modalBody;
	}
	
	function addButton($label,$attrs) {
		$this->_buttons[$label]=$attrs;
	}

	function create($loaderText=null, $hasMessages=false) {
		$html = new ADD_Content_Html();
		
		$htmlAttrs = $this->_htmlAttrs;
		if(!isset($htmlAttrs['class'])) $htmlAttrs['class']="";
		$htmlAttrs['class'].=" modal hide fade";
		$htmlAttrs['id']=$this->_id;
        $htmlAttrs['data-backdrop']="static";
        $htmlAttrs['data-keyboard']="false";
        $html->startElement("div",$htmlAttrs);
		$html->startElement("div",array("class"=>"modal-header"));
		$html->addTextElement("button","×",array("class"=>"close","data-dismiss"=>"modal","type"=>"button"));
		$html->addTextElement("h3",$this->_title,array("id"=>$this->_id."-title","class"=>"alert-heading"));
		$html->endElement("div");
		$html->startElement("div",array("class"=>"modal-body"));
		$html->startElement("p",array("class"=>"muted","ng-show"=>"loadingModal==true"));
		if($loaderText) {
			$html->addHtml($loaderText);
			$html->addElement("img",array("src"=>"/module/clinicle/client/images/ajax-loader.gif","ngShow"=>"loadingModal==true"));
		}
		$html->endElement("p");
		if($hasMessages) {
			$html->addTextElement("div","",array("id"=>$this->_id."-messages"));
		}
		$html->startElement("div",array("class"=>"modal-content"));
		$html->addHtml($this->_modalBody);
		$html->endElement("div");
		$html->endElement("div");
		$html->startElement("div",array("class"=>"modal-footer"));
		
		foreach($this->_buttons as $label => $battrs) {
			if(!isset($battrs['class'])) $battrs['class']="";
			$battrs['class']=$battrs['class']." btn";
			$html->addTextElement("a",$label,$battrs);
		}
		
		$html->endElement("div");
		$html->endElement("div");
		return $html;
	}
}
?>