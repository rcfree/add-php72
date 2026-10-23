<?php

class ADD_Component_ConditionalTopSection extends ADD_Component {
    private $alreadyAdded=array();
	function init() {
		CLE::sys()->getEvents()->addListener(\ClinicLE\System\Event\SystemEvents::AFTER_RESOLVE,array($this,"onAfterResolve"));
	}
	function isAlreadyAdded($sectionName) {
	    return array_key_exists($sectionName,$this->alreadyAdded);
    }
	function onAfterResolve(\ClinicLE\Page\Event\PageEvent $event)
    {
        $resolvePage = $event->getPage();
        $parentPage = $resolvePage->getParent();
        if (!$parentPage) {
            return;
        }
        if ($parentPage != $this->getPage()) {
            return;
        }
        if($this->isAlreadyAdded($parentPage->getName())) {
            return;
        }
        $this->baseContent();
        $this->alreadyAdded[$parentPage->getName()]=true;
    }
    function baseContent() {
		foreach($this->getPage()->getChildPages() as $childPage) {
			if($childPage->getSetting('ConditionalTopMenu.never_show')) continue;
			if($childPage->getSetting('no-menu')) continue;
			$childPage->getComponents()->add("ConditionalTopMenu");
		}
	}
}
?>
