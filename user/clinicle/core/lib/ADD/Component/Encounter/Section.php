<?php



class ADD_Component_Encounter_Section extends ADD_Component_Encounter {
    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
        
        $er = ADD::sys()->getEncounterManager()->sectionByPatientAndType(ADD::sys()->getPatientId(), $page->getSetting('encounter'));
		if(!$er->exists()) {
			$er->store();
		}
        return $er;
    }

    function title(&$title) {
        $page = $this->_page;
		$title = $page->getSetting('title');
    }
  
    public function menu($options) {
    	$add = ADD::sys();
        $page = $this->_page;
        $title = $page->getSetting('title');
        
        $er = $page->getEncounterRecord();
       	
		$subOptions = array();
		
		$hasChildPermission=false;
        foreach ($page->getChildPages() as $childPage) {
            if ($childPage->hasAnyPermissions(array('view','edit','create'))) {
                $subOption = $childPage->createMenu();
                if ($subOption->countElements()>0) $subOptions[] = $subOption;
                $hasChildPermission=true;
            }
        }
        
        
        if (!$er->exists() && $hasChildPermission) {
                $options->startElement('ul', array('class' => 'nav nav-list'));
        	$options->startElement("li");
                $options->addTextElement("a", "<i class='icon-plus'></i>&nbsp;Add patient to " . $page->getSetting('label') . " section..", array("click"=>"ADD.Notice.startWait()", "href" => ADD_Util::uri($page->createMenuPath(), array("t" => ADD_Util::token(), "manual" => 1, "pid" => $add->getPatientId()))));
           	$options->endElement("li");
                $options->endElement('ul');
                return;
        }
        if (count($subOptions) == 0) {
             $options = null;
             return;
        }
	
		
        $options->startElement('ul', array('class' => 'nav nav-list'));
        $options->addTextElement("li", $title, array('class' => 'nav-header'));
		$options->addHtml($subOptions);
        $options->endElement('ul');
    }

    function menuPath(&$menuPath) {
        $page = $this->_page;
        $menuPath = $page->getParentPage()->getName() . "/" . $page->getName() . "/new";
    }

    function generate() {
        $page = $this->_page;
	if (!ADD::db('token')->register()) {
            $page -> addError("You have already added a '" . $page -> getSetting('title') . "'. To ensure data integrity you can only add one at a time.");
 		$page->redirectUsingSetting();
            return;
        

		}

		$er = $page->getEncounterRecord();
		
		if ($er->exists()) throw new ADD_EncounterException("Patient already added the " . $page->getSetting('title') . " section");
        $addMessage = $page->getSetting('add_message', "Patient was added to the " . $page->getSetting('title') . " section successfully");
        $er->store();
        	
        $page->addMessage($addMessage);
        $page->redirectUsingSetting();
        
    }

}

?>
