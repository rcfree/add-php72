<?php



class ADD_Component_Encounter_PrelimSection extends ADD_Component_Encounter {
    protected function initEncounterRecord($idValue=null) {
        return null;
    }

    function title(&$title) {
        $page = $this->_page;
	$title = $page->getSetting('title');
    }
  
    public function menu($options) {
        $page = $this->_page;
        $title = $page->getSetting('title');
        
        $subOptions = array();
		
        foreach ($page->getChildPages() as $childPage) {
            if ($childPage->hasAnyPermissions(array('view','edit','create'))) {
                $subOption = $childPage->createMenu();
                if ($subOption->countElements()>0) $subOptions[] = $subOption;
            }
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

}

?>