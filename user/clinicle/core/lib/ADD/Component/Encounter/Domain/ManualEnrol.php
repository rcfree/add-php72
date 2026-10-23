<?php



class ADD_Component_Encounter_Domain_ManualEnrol extends ADD_Component_Encounter_Domain {
	private $_deceased=false;
	function isDeceased() {
		return $this->_deceased;
	}
	function setDeceased($deceased) {
		$this->_deceased=$deceased;
	}
  
    function displayAccess() {
        $add = ADD::sys();
        $pid = $add->getPatientId();
        $page = $this->_page;
        if (ADD_Util::isEmpty($pid)) throw new ADD_PatientException("The patient ID is null");

        //check user has create permission
        if (!$this->canAddToDomain()) {
            throw new ADD_AccessException("Sorry you do not have permission to add the patient to a " . $page->getSetting('title'));
        }
    }

    function generate() {
        $page = $this->_page;
        if (!ADD::db('token')->register()) {
            throw new ADD_ComponentException("It appears that you may have tried to add this patient to the '" . $page->getSetting('title') . "' domain multiple times. They have already been added to this domain.");
        }
        
        $this->_enrolInDomain();
		
        if ($page->getErrors()) return;
        $addMessage = $page->getSetting('add_message', "Patient was added to the '" . $page->getSetting('title') . "' domain successfully");
        
        $page->addMessage($addMessage);
        $page->redirectUsingSetting();
    }

    public function menu($options) {
        $page = $this->_page;
		$add = ADD::sys();
        $pid = $add->getPatientId();
        if (ADD_Util::isEmpty($pid)) {
			return;
		}

		if(!$add->getCurrentPage()) throw new Exception("Current page not found");
        if ($add->getCurrentPage()->hasComponent('RemovePatient'))
            return;

        $patient = ADD::db('patient')->getPatient($pid);
		 if (!$patient || $patient['is_deleted']) {
			return;
		}
		if(isset($patient['deceased']) && $patient['deceased']) $this->setDeceased(true);

        $er = $page->getEncounterRecord();
        if ($er->exists()) {
			$this->_addDomainOptions($options);
            return;
        }

        $optHtml = null;
        $hasPrelimPages = $this->hasAccessiblePrelimPages();
		$canAdd = $this->canAddToDomain();
		
        if (!$canAdd && !$hasPrelimPages) return;
		
		$this->_addDomainTitle($options);
		$this->_startDomainOptions($options);
		$this->_addDomainPrelimPages($options);
		if ($canAdd) $this->menuDomainAddOption($options);
		$this->_endDomainOptions($options);
    }
	function hasAccessiblePrelimPages() {
		$modPages = $this->_page->getChildPages();
		foreach ($modPages as $modPage) {
			if(!$modPage->hasComponent('Encounter')) continue;
			$comp = $modPage->getComponent('Encounter');
			if(get_class($comp)=="ADD_Component_Encounter_PrelimSection") {
				if($modPage->createMenu()->countElements()>0) return true;
			}
		}
	}
	function canAddToDomain() {
		return $this->_page->getPermission('create');
	}
	function menuDomainAddOption($options) {
		$page = $this->_page;
		$add = ADD::sys();
        
        $pid = $add->getPatientId();
		$href = $page->createMenuUri();
		$options->startElement('ul', array('class' => 'nav nav-list'));
		$options->startElement("li");
		
		$options->addTextElement('a', "<i class='icon-plus-sign'></i>&nbsp;" . $page->getSetting('create-label', "Add patient to " . $page->getSetting('title') . ".."), array('onclick'=>'ADD.Notice.startWait()','href' => $href));
		$options->endElement("li");
		$options->endElement('ul');
	}
    function menuPath(&$menuPath) {
        $page = $this->_page;
        $menuPath = $page->getModule()->getName();
    }
	function menuParams(&$menuParams) {
		$menuParams[ADD_Page::PID]=ADD::sys()->getPatientId();
		$menuParams[ADD_Page::T]=ADD::db('token')->generate();
		$menuParams["a"]="create";
	}
    function expectedPermissions(&$expPerms) {
        $expPerms+=array('create');
    }

}
