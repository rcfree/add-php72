<?php



class ADD_Component_Encounter_Domain extends ADD_Component_Encounter {

    protected function initEncounterRecord($idValue=null) {
        $page = $this->_page;
        $pid = ADD::sys()->getPatientId();
        $er = ADD::sys()->getEncounterManager()->domain($page->getSetting('encounter'),$pid);
        return $er;
    }

    function isDomainRequired() {
        return true;
    }
    protected function _enrolInDomain() {
        $page = $this->_page;
        $pid = ADD::sys()->getPatientId();
        if (ADD_Util::isEmpty($pid)) throw new ADD_PatientException("The patient ID is null");

        $er = $page->getEncounterRecord();
		
       	if ($er->exists()) throw new ADD_EncounterException("It appears that you may have tried to add this patient to the '" . $page->getSetting('title') . "' domain multiple times. They have already been added to this domain.");
        $er->store();
      
        $createSecondaries = $page->getSettingList('create-set', ',');
		$ids = array();
        if (count($createSecondaries) > 0) {
            $module = $page->getModule();
            foreach ($createSecondaries as $createSecondary) {
                $spage = $module->getPage($createSecondary);
                if (!$spage)
                    throw new ADD_PageException("Page '" . $createSecondary . "' not found in module");
                $encType = $spage->getSetting('encounter');
                $cer = ADD::sys()->getEncounterManager()->newSection($pid,$encType);
                $cer->store();
				$ids[]=$cer->getId();
             }
        }
		if(count($ids)==0) return;
		ADD::sys()->setParam('eid',$ids[0]);
    }
    
    protected function _addDomainOptions($options, $canCreate = false) {
    	$page = $this->_page;
        $modPages = $page->getChildPages();

        $tempOpts = ADD::web()->newContent();

		$subOptions = array();
		$numPrelimSections=0;
        foreach ($modPages as $modPage) {
        	
			$optHtml = $modPage->createMenu();
		
            if ($optHtml->countElements() == 0) continue;
			if(get_class($modPage->getComponent('Encounter'))=="ADD_Component_Encounter_PrelimSection") $numPrelimSections++;
			$subOptions[]=$optHtml;
		}
		
		$scount = 0;
		if(count($subOptions)>0) {
			foreach($subOptions as $subOption) {
				$tempOpts->addHtml($subOption);
				if ($scount < count($subOptions) - 1) {
					$tempOpts->startElement("ul", array('class' => 'nav nav-list'));
					$tempOpts->addTextElement("li", "", array('class' => 'divider'));
					$tempOpts->endElement("ul");
				}
				$scount++;
			}
		}
		if (count($subOptions)==0 && !$page->getPermission('create')) return;
		
		$this->menuDomainExtraMessage($numPrelimSections,$subOptions, $tempOpts);
		
        $this->_addDomainTitle($options);
        $this->_startDomainOptions($options);
		$options->addHtml($tempOpts);
		
        $this->_endDomainOptions($options);
        
    }
	function menuDomainExtraMessage($numPrelimSections,$subOptions, $options) {
		$extraMessage = null;
		
		
		if($numPrelimSections>0 && count($subOptions)==$numPrelimSections) {
			$extraMessage = "No access to other domain data";
		}
		if(count($subOptions)==0) {
			$extraMessage = "No access to domain data";
		}
		
		if(!$extraMessage) return;
		$this->addDomainMessageItem($options,$extraMessage);
		
	}
	
	function addDomainMessageItem($options,$message, $class="label label-warning") {
		$options->startElement("ul",array('class'=>'nav nav-list','style'=>'padding-top:5px'));
		$options->startElement("li");
		$options->addTextElement("span", $message,array("class"=>$class));
		$options->endElement("li");
		$options->endElement("ul");
	}
    protected function _addDomainTitle($options) {
        $page = $this->_page;
        $id = $page->getModule()->getAlias() . "-domain";
        $add = ADD::sys();
        
		
       	$title = $page->getSetting('title');
		$er = $page->getEncounterRecord();
       	if ($er->exists()) $title.="*";
        $class = $add->hasState('openMenu') == $page->getModule()->getSuperModule()->getName() ? "down-caret" : "right-caret";
        $options->startElement("div", array("class" => "accordion-heading", "id" => $id . "-heading"));
        $options->startElement("h5", array("class" => "accordion-toggle", "data-toggle" => "collapse", "data-parent" => "#options", "href" => "#" . $page->getModule()->getAlias() . "-domain"));
		$options->addHtml(($add->config('options_open') ? "" : "<span class='symbol $class'></span>") . $title);
		$this->menuDomainTitleExtra($options);
		$options->addTextElement("button","<i class='icon-refresh'></i>",array("ng-click"=>"refresh()","class"=>"btn btn-mini pull-right"));
$options->startElement("span",array("style"=>"display:block;height:0.7em;font-size:90%"));
$db = CLE::svc(\ClinicLE\System\Service\CoreServices::MAIN_CONNECTION);

$sql = "select e.date_updated from encounter e left join encounter_form ef on ef.id = e
.encounter_form_id where e.patient_id=? and ef.type like ? order by date_updated desc";
$params[] = $add->getPatientId();
$params[] = $page->getSetting("encounter")."%";
$lastEnc = $db->fetchFirst($sql, $params);
$locale = CLE::svc(\ClinicLE\System\Service\CoreServices::LOCALE);
$lastUpdated = \ClinicLE\Common\Util::val('date_updated',$lastEnc);
        $options->addTextElement("small",\ClinicLE\Common\Util::isEmpty($lastUpdated) ? "NO RECORDS" : "LAST UPDATED: "
            .$locale->isoToDisplayDate($lastUpdated));
        $options->endElement("span");
		$options->endElement("h5");
		$options->endElement("div");


    }
	protected function menuDomainTitleExtra($options) { }
    protected function _startDomainOptions($options) {
        $page = $this->_page;
        $class = "accordion-body collapse";
        $id = $page->getModule()->getAlias() . "-domain";
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

	function _addDomainPrelimPages($options) {
		$modPages = $this->_page->getModule()->getChildPages($this->_page);

		foreach ($modPages as $modPage) {
			if(!$modPage->hasComponent('Encounter')) continue;
			if (get_class($modPage->getComponent('Encounter')) == "ADD_Component_Encounter_PrelimSection") {
				$optHtml = $modPage->createMenu();
				$options->addHtml($optHtml);
			}
        }
	}
	
	function title(&$title) {
		$title = $this->_page->getSetting('title');
	}
}
