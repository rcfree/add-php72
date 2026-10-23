<?php
class ADD_Component_ConfirmSave extends ADD_Component {
	function afterGenerate() {
        if($this->getSetting('disable')) return;
			
        $page = $this->_page;
		$html = $page->html();
		$html->startElement("div",array("id"=>"modal-confirm-save","class"=>"modal hide fade","style"=>"width:500px"));
		$html->startElement("div", array("class"=>"modal-body"));
		$html->addTextElement("div","You have made changes to this record. Would you like to save it before changing the page?",array("id"=>"modal-messages-confirm-save"));
		
		$html->endElement("div");
		$html->startElement("div",array("class"=>"modal-footer"));
		$html->addTextElement("a","Stay on this page",array("href"=>"javascript:;","class"=>"btn ignoredirty","id"=>"ignore-confirm","cle-nong"=>""));
		$html->addTextElement("a","No, thanks",array("href"=>"javascript:;","class"=>"btn ignoredirty","id"=>"close-confirm","cle-nong"=>""));
		$html->addTextElement("a","Yes, please",array("href"=>"javascript:;","class"=>"btn btn-primary ignoredirty","id"=>"save-confirm","cle-nong"=>""));
		$html->endElement("div");
		$html->endElement("div");
		$page->addJavascript("ADD.Util.activateDirty();");
	}	
	function beforeGenerate() {
        if($this->getSetting('disable')) return;	
        $page = $this->_page;
		$page->html()->addElement('input',array('type'=>'hidden','value'=>'','name'=>'__confirm_redirect'));
	}
	
	function afterSubmit(ADD_Page $submitPage) {
		if($this->getSetting('disable')) return;
		
		$add = ADD::sys();
        if (!$submitPage->hasComponent('Form')) return;
		$form = $submitPage->getComponent('Form');
		$confirmRedirect = $form->getUncleanValue('__confirm_redirect');
		if (!$confirmRedirect) return;
		$add->setState('redirect',$confirmRedirect);
	}
}