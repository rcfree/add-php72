<?php
use ClinicLE\Security\AccessManager;

class ADD_Field_DownloadLink extends ADD_Field_Hidden {
	
	public function addMarkup($page) {
		parent::addMarkup($page);
		$html = ADD::web()->getContent();
		$am = new AccessManager();
		$linkPage = $am->getCurrentPage($this->getSetting('link_path'));
		$html->startElement("div",array("style"=>"margin-bottom:16px"));
		if(!$this->getValue()) {
			$html->addTextElement("span",$this->getSetting('no_file_text',"No ".$this->getTitle()." available to view"),array("class"=>"label-info label"));
			$html->endElement("div");
			return;
		}
		
		if(!$linkPage->getPermission('view')) {
			$html->addTextElement("span",$this->getSetting('no_permission_text',"No permission to view ".$this->getTitle()),array("class"=>"label-important label"));
			$html->endElement("div");
			return;
		}
		
		$href = $this->assertSetting('link_path')."?file=".$this->getValue();
		$icon = $this->getSetting('icon');
		$class = $this->getSetting('button_class','');
		$html->addHtml("<![if !IE]>");
		$html->addTextElement("a",($icon ? "<i class='$icon'></i>&nbsp;" : "")."View ".$this->getTitle(),array("class"=>"btn btn-small $class","href"=>$href,"target"=>"_blank","cle-nong"=>1));
		$html->addHtml("<![endif]>");
		$html->addHtml("&nbsp;");
		$html->addTextElement("a",($icon ? "<i class='$icon'></i>&nbsp;" : "")."Download ".$this->getTitle(),array("class"=>"btn btn-small $class","href"=>$href."&download=1","cle-nong"=>1));
		//$html->addHtml("<!--[if IE]>");
		//$html->addTextElement("p","The 'View PDF report file' option does not work in Internet Explorer",array("class"=>"text-info"));
		//$html->addHtml("<![endif]-->");
		
		
		$html->endElement("div");
	}
}
?>