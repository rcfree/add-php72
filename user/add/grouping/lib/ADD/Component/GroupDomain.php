<?php


class ADD_Component_GroupDomain extends ADD_Component_Encounter_Domain {

    function title(&$title) {
        $page = $this->_page;
        $title = $page->getSetting('create-label', "Add Patient to " . $page->getSetting('title'));
    }

    function displayAccess() {
        $add = ADD::sys();
        $pid = $add->getPatientId();
        $page = $this->_page;
        if (ADD_Util::isEmpty($pid)) throw new ADD_PatientException("The patient ID is null");

        //check user has create permission
        if (!$page->getPermission('create')) {
            throw new ADD_AccessException("Sorry you do not have permission to add the patient to a group");
        }
    }

    function generate() {
        $page = $this->_page;
        if (!ADD::db('token')->register()) {
            throw new ADD_ComponentException("It appears that you may have tried to add this patient to the group multiple times.");
        }
        
        $this->_enrolInDomain();
        if ($page->getErrors()) return;
        $addMessage = $page->getSetting('add_message', "Patient was added to the '" . $page->getSetting('title') . "' group successfully");
        
        $page->addMessage($addMessage);
        $page->redirectUsingSetting();
    }

    public function menu($options) {
        $page = $this->_page;
		$add = ADD::sys();
        $log = ADD::log();
        $pid = $add->getPatientId();
        if (ADD_Util::isEmpty($pid)) {
			$log->warn("pid is empty");
			return;
		}
		
        if ($add->getCurrentPage()->hasComponent('RemovePatient'))
            return;
        
        $patient = ADD::db('patient')->getPatient($pid);
        if (!$patient || $patient['is_deleted']) {
			$log->warn("No patient or is deleted");
			return;
		}
		
		$this->_addDomainTitle($options);
		$this->_startDomainOptions($options);
		// $this->_addDomainOptions($options);
		$this->menuDomainAddOption($options);
		$this->_endDomainOptions($options);
    }
	
	function menuDomainAddOption($options) {
		$page = $this->_page;
		$add = ADD::sys();
        
        $pid = $add->getPatientId();
		$menuPath = null;
        $this->menuPath($menuPath, $page);
		$href = ADD_Util::uri($menuPath, array(ADD_Page::PID => $pid, ADD_Page::T => ADD_Util::token()));
            
		$options->startElement('ul', array('class' => 'nav nav-list'));
		$options->startElement("li");
		$options->addTextElement('a', "<i class='icon-plus-sign'></i>&nbsp;" . $page->getSetting('create-label', "Add patient to " . $page->getSetting('title') . ".."), array('onclick'=>'startWait()','href' => $href));
		$options->endElement("li");
		$options->endElement('ul');
	}
    function menuPath(&$menuPath) {
        $page = $this->_page;
        $menuPath = $page->getModule()->getName() . "/add-to";
    }

    function expectedPermissions(&$expPerms) {
        $expPerms+=array('create');
    }
	protected function _startDomainOptions($options) {
        $page = $this->_page;
        $class = "accordion-body collapse";
        $id = $page->getModule()->getName() . "-domain";
        $add = ADD::sys();
        if ($add->hasState('openMenu') == $page->getModule()->getSuperModule()->getName() || $add->config('options_open'))
            $class.=" in";
        $options->startElement("div", array("class" => $class, "data-parent" => "#options", "id" => $id));
        $options->startElement("div", array("class" => "accordion-inner"));
    }

    protected function _endDomainOptions($options) {
        $options->endElement("div");
        $options->endElement("div");
    }
	protected function _addDomainTitle($options) {
        $page = $this->_page;
        $id = $page->getModule()->getName() . "-domain";
        $add = ADD::sys();
       
       	$title = $page->getSetting('title');
		$er = $page->getEncounterRecord();
        $class = $add->hasState('openMenu') == $page->getModule()->getSuperModule()->getName() ? "down-caret" : "right-caret";
        $options->startElement("div", array("class" => "accordion-heading", "id" => $id . "-heading"));
        $options->startElement("h5", array("style" => "padding:0 8px", "class" => "accordion-toggle", "data-toggle" => "collapse", "data-parent" => "#options", "href" => "#" . $page->getModule()->getName() . "-domain"));
		$options->addHtml(($add->config('options_open') ? "" : "<span class='symbol $class'></span>") . $title);
		$this->menuDomainTitleExtra($options);
		$options->endElement("h5");
        $options->endElement("div");
    }
}
