<?php

class ADD_Field_Button extends ADD_Field_Base {
    protected $stored = false;
	protected function getOnClick($page) { }
	function addMarkup($page) {
		$html = $page->html();
		if (!$this->isAvailable($page)) return;       
		$title = $this->getLinkTitle($page);


        $attrs = $this->getFieldHtmlAttrs($page);
		$btn = array(
					'tag'=>'a',
					'text'=>$title,
					'type'=>ADD_Content_Html::TEXT,
					'attrs'=>$attrs,
				);
		if ($this->getSetting('parent_key')) {
			$html->addBeforeElement(
				'opt_end',
				$btn
			);
		}
		else {
		    if ($this->getSetting('empty_label')) {
		        $html->addTextElement("label","&nbsp;");
		    }
			$html->addHtml($btn,null,true);
		}
	}
	protected function getLinkTitle($page) {
		return $this->getTitle();
	}
	protected function getFieldHtmlAttrs($page) {
        $add = ADD::sys();


        $attrs = array(
			"class"=>$this->getSetting('class','btn'),
			"type"=>"button"
		);

        $onClick=$this->getOnClick($page);

        $linkPath = $add->hasState('validation') ? 'link' : $this->getLinkPath($page);

        if ($onClick) $attrs['onclick']=$onClick;

        if ($linkPath) $attrs['href']=$linkPath;
        if (!$onClick && !$linkPath) $attrs['disabled']='disabled';


        $tooltip=$this->getSetting('tooltip');
		if ($tooltip) {
			$attrs['data-original-title']=$tooltip;
			$attrs['data-toggle']="tooltip";
			$attrs['data-container']="body";
			$attrs['data-html']="true";
		}
		return $attrs;
	}
	protected function getLinkPath($page) {}
	public function createPdfElement($rptField,$page,$currentX,$currentY) {
		return array(0,0);
	}
	public function getRequiredPermission() {
		return null;
	}
	
	public function isAvailable($page) {
	    if($this->isHidden()) {
	        return false;
        }
		$reqPerm = $this->getRequiredPermission();
		if ($reqPerm && !$page->getPermission($reqPerm)) return false;
		return true;
	}

}
?>