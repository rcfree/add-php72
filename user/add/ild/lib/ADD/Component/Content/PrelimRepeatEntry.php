<?php

class ADD_Component_Content_PrelimRepeatEntry extends ADD_Component_Content_PrelimEntry {
	protected $_componentList = array('PatientCheck','Encounter_Single_RepeatPrelim', 'Form_DB','AccessControl','OptionContainer','SaveButton','ConfirmSave','Locking','RequiredFields_Notify','HistoryOption');
	
	protected function createMenuItems($options,$label, $eid, $canEdit) {
		$page = $this->_page;
		$add = ADD::sys();
		$erCount=1;
		
		$ers = $this->records($add->getPatientId(),$page->getSetting('encounter'));
		$oattrs = array();
		$menuPath = $page->createMenuPath();

		foreach($ers as $er) {
			$er->retrieve();
			if(ADD::sys()->getAccessManager()->isCurrentPage($page) && $eid == $er->getId()) {
				$oattrs=array("class"=>"active");
			}
			else {
				$oattrs=array();
			}
			$options->startElement('li',$oattrs);	
			$menuParams = array("eid"=>$er->getId(),"pid"=>$add->getPatientId(),"action"=>($canEdit ? "edit" : "view"));
			$menuUri = ADD_Util::uri($menuPath,$menuParams);
			$attrs=array("href"=>$menuUri);

			$options->startElement('a',$attrs);
			$options->addHtml($label." ".$erCount);

			$options->endElement('a');

			$options->endElement('li');
			$erCount++;
		}
	}
	public function menu($options) {
        $page = $this->_page;

		list($create,$view,$edit)=$page->getPermissions(array('create','view','edit'));
        if (!$create && !$view && !$edit) return null;

		$add = ADD::sys();

		$label = $page->getSetting('single');
		
		if ($page->getExpectedPermissions()!=array('view')) {
			if (!$edit && $view) $label.=" (view only)";
		}
		$eid = $add->getParam('eid');
		
		
		if($edit || $view) {
			$this->createMenuItems($options,$label, $eid,$edit);
		}
		
		if(!$create) return;
		$oattrs=$add->getParam('action')=="create" && !$eid ? array("class"=>"active") : array();
		$options->startElement("li",$oattrs);
		$href=ADD_Util::uri($page->createMenuPath(),array("pid"=>$add->getPatientId(),"action"=>"create"));
		$options->addTextElement('a', "<i class='icon-pencil'></i>&nbsp;" . $page->getSetting('create-label', "Create referral.."), array('onclick'=>'ADD.Notice.startWait()','href' => $href));
		$options->endElement("li");
			
    }
	
	function records($patientId, $type) {
		$cers = array();
		
		$edb = ADD::db('encounter');
                
		$cencs = $edb -> getEncountersWithPatientAndType($patientId, $type);
		if (!$cencs || count($cencs)==0) return array();
		foreach ($cencs as $cenc) {
			$cer = new \ClinicLE\Data\Record\EncounterRecord();
			$cer -> setEncounter($cenc);
			$cer->setParent(null);
			$cers[] = $cer;
		}
		return $cers;
	}
	
	function expectedPermissions(&$expPerms) {
		$expPerms=array("delete","view","edit","create");
	}
}
