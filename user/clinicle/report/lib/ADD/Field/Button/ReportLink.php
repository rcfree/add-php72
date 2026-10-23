<?php

class ADD_Field_Button_ReportLink extends ADD_Field_Button {
	public function isAvailable($page) {
		$reportPage = $page->getModule()->getPage($this->getSetting('report'));
		if(!$reportPage) return true;
		if(!$reportPage->getPermission('print')) return false;
		return true;
	}
	public function beforeAddMarkup($page) {
		$add = ADD::sys();
		if (!$this->getSetting('no_tooltip') && !$add->hasState('readOnly')) {
			$this->defaultSetting("tooltip","If you have not done so, please save changes before opening the '".$this->gettitle()."'");
		}
	}
	protected function getLinkTitle($page) {
	    if ($page->hasComponent('Encounter')  && !$this->getSetting('use_encounter')=="parent") {
            $er = $page->getEncounterRecord();
		    return $er->exists() ? "<i class='icon-print'></i>&nbsp;".$this->getTitle() : "Save to ".lcfirst($this->getTitle());
        }
		else {
			return "<i class='icon-print'></i>&nbsp;".$this->getTitle();
		}
	}
	
	protected function getLinkPath($page) {
		$add = ADD::sys();
		$reportName = $this->getSetting('report');
				
		if ($page->hasComponent('Encounter') && !$this->getSetting('use_encounter')=="parent") {
			$er = $page->getEncounterRecord();
            if ($er->exists()) {
			     return ADD_Util::uri("report",array('type'=>is_a($er,"\\ClinicLE\\Data\\Record\\SectionRecord") ? "section" : "encounter",'id'=>$er->getId(),'name'=>$reportName,'pid'=>$add->getPatientId(),'page'=>$page->getName(),'module'=>$page->getModule()->getName()));
            }
        }
		else {	
			$ppage = $page->getParentPage();
			if ($ppage->hasComponent('Encounter')) {
				$per = $ppage->getEncounterRecord();
				return ADD_Util::uri("report",array('type'=>is_a($per,"\\ClinicLE\\Data\\Record\\SectionRecord") ? "section" : "encounter",'id'=>$per->getId(),'name'=>$reportName,'pid'=>$add->getPatientId(),'page'=>$page->getName(),'module'=>$page->getModule()->getName()));
			}
		}
	}
	
	protected function getOnClick($page) {
		if($this->getSetting('refresh_field')) {
			return "CLE.Client.refreshAfter(1000,'" . $this->getSetting('refresh_field')."')";
		}
	}
	
	protected function getFieldHtmlAttrs($page) {
		$attrs = parent::getFieldHtmlAttrs($page);
	  $attrs['cle-nong']=1;
		return $attrs;
	}
}
?>