<?php



class ADD_Component_Encounter_RepeatSection extends ADD_Component_Encounter {

	protected function initEncounterRecord($idValue=null) {
		$page = $this->_page;
		
		try {
			$er = ADD::sys()->getEncounterManager()->sectionById($idValue);
		}
		catch(ADD_EncounterNotFoundException $ex) {
			if(preg_match("/There was no .+ child present/",$ex->getMessage())) {
				throw new ADD_EncounterNotFoundException("There was no ".$page->getSetting('title')." record found for this patient (".$per->getPatient().") matching the supplied ID (".$idValue.")");
			}
			throw $ex;
		}
		$er->retrieve();
		return $er;
	}
	function displayAccess() {
		$action = ADD::sys()->getParam('action','create');
		if(!$this->_page->getPermission($action)) throw new ADD_NoPermissionToAccessException("You do not have permission to '$action' a ".$this->_page->getSetting('title'));
		ADD::sys()->setState('action',$action);
	}
	
	function title(&$title) {
        $page = $this->_page;
		$action = ADD::sys()->getParam('action');
		if($action && $action=="create") {
			$title = "New ".$page->getSetting('label');
			return;
		}
		$this->_populateFieldAndPattern();
		if(!ADD::sys()->getParam('eid')) {
			$title=null;
			return;
		}
		$title = $this->menuSectionLabel($this->getEncounterRecord(ADD::sys()->getParam('eid')));
	}
	
	protected $labelField;
	protected $labelPage;
	protected $showMaxLen;
	protected $sectionCount;
	protected $noLinks = false;
	
	private $_childRecords = array();
	
	public function _getChildRecord(ADD_EncounterRecord $parentRecord, ADD_Page $childPage) {
		$key = $childPage->getName().".".$parentRecord->getId();
		if(isset($this->_childRecord[$key])) {
			return $this->_childRecords[$key];
		}
		$labelEr = ADD::sys()->getEncounterManager()->child($parentRecord,$childPage->getSetting('encounter'));
		$labelEr->retrieve();
		$this->_childRecords[$key]=$labelEr;
		return $labelEr;
	}
	public function _getChildPageAndField($page, $pageBits) {
		$labelPage = $page -> getChildPage($pageBits[0]);
		if (!$labelPage) throw new ADD_PageException("Unable to get child page '{$pageBits[0]}' of '".$page->getName()."'");
		$labelField = $pageBits[1];
			
		return array($labelPage,$labelField);
	}
	public function _createLabelFromPattern($pattern,ADD_EncounterRecord $encounterRecord, $sectionCount) {
		$encLabel = null;
		$add = ADD::sys();
		
		$matches = array();
		preg_match_all('/\{(.+?)\}/',$pattern,$matches);
		
		$showLabel = $pattern;
		$page = $this->_page;
		
		foreach($matches[1] as $match) {
			if($match=="section_no") {
				$showLabel = str_replace('{'.$match.'}',$sectionCount,$showLabel);
				continue;
			}
			if($match=="date_updated") {
				$showLabel = str_replace('{'.$match.'}',ADD_Util::dateFromISOToDMY($encounterRecord -> getEncData('date_created')),$showLabel);
				continue;
			}
			$temp = explode(".",$match);
			list($labelPage, $labelField) = $this->_getChildPageAndField($page, $temp);
			$labelEr = $this->_getChildRecord($encounterRecord,$labelPage);
			
			$showValue = $labelEr->getObsText($labelField,$labelPage);

			if ($this->showMaxLen && strlen($showValue)>$this->showMaxLen) {
				$showValue = substr($showValue,0,$this->showMaxLen)."..";
			}
			if(strlen($showValue)==0) $showValue=lcfirst($page->getSetting('empty_pattern_part',ADD_Util::nameToLabel($labelField)." not specified"));
			$showLabel = str_replace('{'.$match.'}',$showValue,$showLabel);
		}
	
		return $showLabel;
	}
	
	public function menuSectionLabel($sectionEr) {
		
		$showLabel = $this->_createLabelFromPattern($this->_showPattern,$sectionEr,$this->sectionCount);
		if(!$showLabel) {
			$page = $this->_page;
			$showLabel = $this->_createLabelFromPattern($page->getSetting('show_missing_pattern',$page->getSetting('label').' {section_no}'),$sectionEr,$this->sectionCount);
		}
		
		return "&raquo;&nbsp;".$showLabel;
	}
	
	function _populateFieldAndPattern() {
		$page = $this->_page;
		$showField = $page -> getSetting('show_field');
		$showPattern = $page->getSetting('show_pattern');
		if(!$showPattern) {
			$showPattern = $page -> assertSetting('label') . " {section_no}";
			if($showField) {
				$showPattern.=" ({".$showField."})";
			}
		}
		$this->_showPattern = $showPattern;
		$this->showMaxLen = $page -> getSetting('show_maxlen');

		if ($showField) {
			$shows = explode(".", $showField);
			$this->labelPage = $page -> getChildPage($shows[0]);
			if (!$this->labelPage) throw new ADD_PageException("Unable to get child page '{$shows[0]}' of '".$page->getName()."'");
			$this->labelField = $shows[1];
		}
	}
	public function menuSectionItems() {
		$add = ADD::sys();
        $options = new ADD_Content_Html();
		$page = $this->_page;

		$this->_availPages = ADD::sys()->getAccessManager()->getAvailChildPages($page);
		
		$create = $page -> getPermission('create');
		
		$anyAvail = count($this->_availPages)>0 ? true : false;
		
		if (!$create && !$anyAvail)
			return array(0,$options);

		$patientId = $add -> getPatientId();

		$ppage = $page -> getParentPage();
		if(!$ppage) throw new ADD_PageNotFoundException("Parent page of '".$page->getName()."' (in module ".$page->getModule()->getName().") does not exist or was not defined");
		
		$cers = ADD::sys()->getEncounterManager()->sectionsByPatientAndType($patientId,$page -> getSetting('encounter'));
		$cerCount = count($cers);

		if (!$create && $cerCount == 0) {
				return array(0,$options);
		}
		$this->_basePath = $ppage -> getName() . "/" . $page -> getName();
		
		$this->_populateFieldAndPattern();
		
		if ($anyAvail) {
			$this->sectionCount = 1;
			if ($cers) {
				$this->menuSectionOpen($options);
				foreach ($cers as $cer) {
					$this->menuSectionOption($cer,$options);
					$this->sectionCount++;
				}
				$this->menuSectionClose($options);
			}
		}
		else {
			if ($cerCount>0 && $create) {
					$options->addTextElement("li","You do not have permission to view ".ADD_Util::pluralize($cers,$page->getSetting('label')));
			}
		}
		return array($cerCount,$options);
	}
	public function menuSectionOpen($options) {}
	public function menuSectionClose($options) {} 
	public function menuSectionOption($cer,$options) {
		$page = $this->_page;
		$attrs = $this->menuSectionAttrs($cer);
		$encLabel = $this->menuSectionLabel($cer);
		
		$options -> startElement("li", $attrs);
		$eid =  $cer -> getId();
		
		$patientId = ADD::sys()->getPatientId();
		
		if(!$this->noLinks) {
			$options -> startElement("a",array("style"=>"","href" => ADD_Util::uri($this->_basePath . "/" . $this->_availPages[0]->getName(),array("pid"=> $patientId,"eid"=> $eid))));
		}
		
		$options -> addHtml($encLabel);
		
		if(!$this->noLinks) {
			$options->endElement("a");
		}
		
		$options -> endElement("li");
		
		if(!$page->getPermission('delete')) return;
		$deleteUri =  ADD_Util::uri($this->_basePath,array("pid"=> $patientId,"eid"=> $cer -> getId(),"t"=>ADD_Util::token(),"action"=>"delete"));
		$deleteName = $page->getName()."_delete_".$eid;
		$options->addTextElement("a","Delete..",array("class"=>"btn btn-mini","name"=>$deleteName,"style"=>"","href"=>"javascript:;","onclick"=>"ADD.Notice.showConfirm('Delete records','This " . $page->getSetting("title")." will be deleted in the database. <br/>Are you sure you want to do this?','"
			.$deleteUri."')"));

	}
	public function menu($options) {
		$create = $this->_page -> getPermission('create');
		list($cerCount,$items) = $this->menuSectionItems($options);
		
		if(!$create && $cerCount==0) return;
		
		if ($create || $cerCount > 0) {
			$this->menuSectionTitle($options,$cerCount);
		}
		$options->addHtml($items);
		
		if ($create) {
			$this->menuSectionAdd($options);
		}

		$options -> endElement("ul");
	}
	
	public function menuSectionAttrs($sectionEr) {
		$attrs = array();
		if($this->_page->isActiveMenu($sectionEr)) {
			$attrs['class']='active';
		}
		return $attrs;	
	}
	
	public function menuSectionTitle($options,$entryCount) {
		$title = $this->_page -> getSetting('title');
		
		$options -> startElement('ul', array('class' => 'nav nav-list'));
		$options -> addTextElement("li", $title . " ($entryCount)", array('class' => 'nav-header'));
	}
	
	public function activeMenu(&$isActive, $currPage, $currentSectionER) {
		$currPPage = $currPage -> getParentPage();
		$isActive = false;
		
		if (!$currPPage) return;
		if($this->_page !== $currPPage) return;
		$eid = ADD::sys()->getParam('eid');
		if(!$eid) {
			$isActive=false;
			return;
		}
		$cper = $currPPage -> getEncounterRecord($eid);
		if(!$cper) return;
		if (!$cper -> exists()) return;
		if ($currentSectionER -> getId() == $cper -> getId()) {
			$isActive=true;
			return;
		}
	}
	
	public function menuSectionAdd($options) {
		$add = ADD::sys();
		$page = $this->_page;
		$ppage = $page -> getParentPage();
		
		$label = $page -> getSetting('label');
		
		$basePath = $ppage -> getName() . "/" . $page -> getName();
		
		$options -> startElement("li", array());
		$onclick = "ADD.Notice.startWait()";
		$params = array("pid"=>$add->getPatientId(),"t"=>ADD_Util::token(),"action"=>"create");
		$newText = $this->getSetting("new_label","New " . $label . '..');
		$options -> addTextElement("a", "<i class='icon-plus'></i>&nbsp;$newText", array("href"=>ADD_Util::uri($basePath,$params),"onclick" => $onclick));
		$options -> endElement("li");
	}
		
	public function generate() {
		$add = ADD::sys();
        $page = $this->_page;
		
		if (!ADD::db('token')->register()) {
			$page -> addError("You have already added a '" . $page -> getSetting('title') . "'. To ensure data integrity you can only add one at a time.");
			return;
		}

		$pid = $add -> getPatientId();

		$availPages = ADD::sys()->getAccessManager()->getAvailChildPages($page);
		
		if($add->getParam('action')=='delete') {
			$eid = $add->getParam('eid');
			$cer = $add->getEncounterManager()->sectionById($add->getParam('eid'));
			if(!$cer->exists()) throw new ADD_EncounterNotFoundException($page -> getSetting('title')." with ID '$eid' does not exist");
			$cer->retrieve();
			$cer->delete();
			$page -> addMessage($page -> getSetting('title') . " record was deleted successfully");
			$add->setState('cancel',true);
			return;
		}
		
		$encName = $page -> getSetting('encounter');

		$cer = $add->getEncounterManager()->newSection($pid,$encName);

		$cer -> store();
		
		if (count($availPages) == 0) {
			$page -> addWarning("You do not have permission to view any Pages associated with '" . $page -> getSetting('title') . "' records");
			ADD::db('token')->unregister();
			return;
		}
		$redirectPath = ADD_Util::uri($availPages[0] -> createMenuPath(), array("pid" => $pid, "eid" => $cer -> getId()), true);
		$add -> setState('redirect', $redirectPath);
		$add -> setState('eid', $cer -> getId());
		$add->setParam('eid',$cer->getId());

	}

	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('create','delete');
	}

	function menuPath(&$menuPath) {
        $page = $this->_page;
		$ppage = $page -> getParentPage();
		$menuPath = $ppage -> getName() . "/" . $page -> getName();
	}
}
?>
