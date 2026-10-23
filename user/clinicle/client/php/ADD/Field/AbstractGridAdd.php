<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


abstract class ADD_Field_AbstractGridAdd extends ADD_Field_Base {
	protected $stored=false;
	function initAttrs() {
		return array(
			"id"=>$this->getName(),
			"ng-controller"=>"AddItemController",
			"cle-parentid"=>$this->getSetting('ignore_parent') ? null : ADD::sys()->getParam('eid'),
			"cle-pid"=>ADD::sys()->getPatientId(),
			"cle-refresh"=>$this->getSetting('refresh'),
			"cle-title"=>$this->getTitle()
		);
	}
	function createHtml($html, $page) {
		if(!$this->isAvailable($page)) return "";
		$usePage = $this->getFieldData("Options");
		$attrs = $this->initAttrs();
		if($this->getHtmlClass()) {
			$attrs['class']=$this->getHtmlClass();
		}
		if (count($usePage)==1) {
			$usePageNames = array_keys($usePage);
			$attrs["cle-pim"]=$usePageNames[0];
		}
		if(count($usePage)==0) {
			$attrs["cle-pim"]=$this->assertSetting("use_page");
		}
		foreach($this->getSettings() as $name=>$setting) {
			$attrs["cle-".$name]=$setting;
		}
		$html->startElement("div",$attrs);
		if($this->getSetting("empty_label")) {
		    $html->addTextElement("label","&nbsp;");
        }
		$this->addGridAddHtml($html);
		
        $modal = $this->createModal();
		
		$html->addHtml($modal->create("Loading content...",true));
		$html->endElement("div");
	}
	
	function createMarkup($page) {
		$html = $page->html();
		$html->addHtml($this->createHtml($html,$page));
		
	}
	function getHtmlClass() {
		return "content-".$this->getName();
	}
    function createModal() {
        $modal = new ADD_Content_Html_ModalHelper($this->getTitle(),$this->getName()."-modal",array("style"=>"width:".$this->getSetting('modal_width','700')."px"));
        $modal->addButton("<i class='icon-remove'></i>&nbsp;Close",array("ng-click"=>"closeItem()"));
        
        $modal->addButton("<i class='icon-plus icon-white' ng-hide='saving'></i><img src='/module/clinicle/client/images/ajax-button-white.gif' ng-show='saving'/> &nbsp;Save item",array("ng-click"=>"saveItem()","class"=>"btn-primary save-button"));
        if($this->getSetting('allow_add_another')) {
            $modal->addButton("<i class='icon-plus icon-white'></i>&nbsp;Save and add another",array("ng-click"=>"saveItemAndAdd()","class"=>"btn-primary save-button"));
        }
        return $modal;
    }
	abstract function addGridAddHtml($html);
	public function isAvailable($page) {
		return true;
	}
}
