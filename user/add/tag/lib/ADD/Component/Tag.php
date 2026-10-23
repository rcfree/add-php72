<?php



class ADD_Component_Tag extends ADD_Component_Encounter {
	
	function menu($options) {
		$add = ADD::sys();
		if(!$add->getPatientId()) return;	
		$page = $this->_page;
	   	if(!$page->getPermission('view')) return;
		$em = ADD::sys()->getEncounterManager();
		
		 $id = $page->getModule()->getName() . "-domain";
	    
	    $options->startElement("div", array("id" => $id));
	    $options->startElement("div");
		
       	$title = $page->getSetting('title');
        $options->startElement("div", array("id" => $id . "-heading"));
        $options->startElement("h5", array("style" => "padding:0 8px", "href" => "#" . $page->getModule()->getName() . "-domain"));
		$options->addHtml("<i class='icon-tag'></i>&nbsp;$title&nbsp;");
		
		$advcopd = $em->domain("advcopd",ADD::sys()->getPatientId());
		if($advcopd->exists()) {
			$options->addTextElement("span","Advanced COPD",array("class"=>"label label-warning"));
		}
		$options->endElement("h5");
        $options->endElement("div");

		$options->endElement("div");
        $options->endElement("div");
	}
	function expectedPermissions(&$expPerms) {
		$expPerms[]="view";
	}
}