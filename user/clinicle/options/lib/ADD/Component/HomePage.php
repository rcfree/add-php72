<?php

class ADD_Component_HomePage extends ADD_Component {
	public function generate() {
        $htManager = CLE::svc(\ClinicLE\Options\Resource\OptionsServices::HOMETOOL_MANAGER);

        $event = new ClinicLE\Options\Event\HomeToolEvent($htManager);
	    CLE::sys()->getEventDispatcher()->dispatch(\ClinicLE\Options\Event\HomeToolEvents::BEFORE_RENDER,
            $event);
		$html = $this->_page->html();
		$htManager->renderHomeTools($html);
	}
	
	public function menuPath(&$menuPath) {
		$menuPath = ADD::sys()->assertConfig("default_path");
	}

	public function title(&$title) {
		$title="Home";
	}
}

