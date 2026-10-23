<?php

class ADD_Field_Container_Tab extends ADD_Field_Container {
	private $_prevTab;
	private $_nextTab;
	
	function startFieldHtml($page) {
		$form = $page->getComponent('Form');
		$childFields = $form->getChildFields($this);
		$parentField = $form->getParentField($this);
		
		$class="tab-pane";
		if ($parentField && $parentField->getSetting('active-tab')==$this->getName()) $class.=" active";

        $page->html()->startElement("div",array("class"=>$class,"id"=>$this->getName()),$this->getName()."_start");
        $page->html()->addTextElement("h5","<u>".$this->getTitle()."</u>",array("class"=>"visible-print-block"));
    }
	function endFieldHtml($page) {
		$page->html()->startElement("div",array("style"=>"margin-top:0.5em;float:left"));
		//$this->_addNaviButtonIfNotNullTab($this->_prevTab,"icon-arrow-left","Previous tab..");
		//$this->_addNaviButtonIfNotNullTab($this->_nextTab,"icon-arrow-right","Next tab..");
		$page->html()->endElement("div");
		parent::endFieldHtml($page);
		
	}
	function _addNaviButtonIfNotNullTab($tab, $icon,$title) {		
		if(!$tab) return;
		$html = ADD::web()->getContent();
		$onclick = '$(\'#tabs-tabs a[href="#'.$tab.'"]\').tab("show");';
		$html->startElement("a",array("class"=>"btn btn-small","onclick"=>"ADD.Form.showTab('".$tab."')"));
		$html->addTextElement("i","",array("class"=>$icon));
		$html->addHtml("&nbsp;$title");
		$html->endElement("a");
	}
	function setPrevTab($prevTab) {
		$this->_prevTab = $prevTab;
	}
	function setNextTab($nextTab) {
		$this->_nextTab = $nextTab;
	}
}
?>