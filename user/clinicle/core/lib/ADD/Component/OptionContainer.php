<?php

class ADD_Component_OptionContainer extends ADD_Component {
    function beforeGenerate() {
        $page = $this->_page;

        $html = $page->html();
		$html->startElement("div",array("class"=>"form-actions","id"=>"buttons","style"=>"padding:0.5em"));
		$html->startElement("div",array("class"=>"btn-group"),"opt_start");
        $page = $this->getPage();
        $event = new \ClinicLE\Component\Event\PageContentEvent($html, $page);
        $page->getEvents()->dispatch(\ClinicLE\System\Event\ContentEvents::OPTION_BUTTONS,$event);
		$html->endElement("div","opt_end");
		$html->endElement("div");

        $page->getEvents()->dispatch(\ClinicLE\System\Event\ContentEvents::AFTER_OPTION_BUTTONS,$event);
	}

	function afterGenerate() {
        $page = $this->_page;
        $page->addJavascript("ADD.assign('ToggleLock','buttons')");
	}

}
?>