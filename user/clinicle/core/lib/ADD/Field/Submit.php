<?php

class ADD_Field_Submit extends ADD_Field_Base implements ADD_Field_SubmitInterface {
    private $page;
    function beforePopulateFields($page) {
        if (!$this->getSetting('parent_key')) {
            return;
        }
		if(!method_exists($page,"getEvents")) {
			return;
		}
        $page->getEvents()->addListener(\ClinicLE\System\Event\ContentEvents::OPTION_BUTTONS,array($this,
            "onOptionButtons"));
        $this->page = $page;
    }
    function onOptionButtons(\ClinicLE\Component\Event\PageContentEvent $event) {
        if($this->isHidden()) {
            return;
        }
        $this->addMarkup($event);
    }
    function createMarkup($page) {
        if($this->isHidden()) {
            return;
        }
        if ($this->getSetting('parent_key')) {
            return;
        }

        parent::createMarkup($page);
    }
    function getFieldAttrs($page) {
        $onClick=$this->getOnClick($page);
        $linkPath = $this->getLinkPath($page);
        $class = $this->getLinkClass($page);
        $attrs = array(
            "name"=>$this->getName(),
            "value"=>"submit",
            "class" => $class,
            "type"=>$this->getButtonType($page),
            "ng-click"=>"save(\$event)",
            "ng-disabled"=>"hasBeenSubmitted()"
        );
        if ($onClick) $attrs['onclick']=$onClick;
        if ($linkPath) $attrs['href']=$linkPath;
        return $attrs;
    }
	function addMarkup($page) {
		$html = $page->html();
		$title = $this->getLinkTitle($page);
        $attrs = $this->getFieldAttrs($page);
		$btn = array(
			'tag'=>'button',
			'text'=>$title,
			'attrs'=>$attrs,
			'type'=>ADD_Content_Html::TEXT
		);

        if ($this->getSetting('empty_label')) {
            $html->addTextElement("label","&nbsp;");
        }
        $html->addHtml($btn,null,true);
	}
	function getLinkClass($page) {
		return "btn";
	}
	function getLinkPath($page) { }
	function getLinkTitle($page) {
		return $this->getTitle();
	}
	function getOnClick($page) { 
		if(ADD::sys()->hasState("readOnly")) return;
		return null;
	}
	
	function isSubmittedField($values) {
		if (ADD_Util::val($this->getName(),$values)=="submit") return true;
	}
	function getButtonType($page) {
		return "submit";
	}
	function submitValues($values, $page) {
	}

	public function beforeSubmitValues(&$values, $page) {
		
	}

}
?>