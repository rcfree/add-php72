<?php

class ADD_Component_ConditionalTopMenu extends ADD_Component {
	private $_conditionMet = false;

	function isAvailable($cpage) {
		$parentPage = $cpage -> getParentPage();
		if (!$cpage -> hasComponent('ConditionalTopMenu')) {
			return false;
		}
		$ctm = $cpage -> getComponent('ConditionalTopMenu');
		$ctm -> setConditionMet($ctm -> _getConditionMetValue());
		if (!$parentPage -> hasComponent('ConditionalTopSection')) {
			throw new ADD_ComponentException("Parent page '" . $parentPage -> getName() . "' does not have 'ConditionTopSection' component");
		}
		if (!$ctm -> isConditionMet() && !$ctm -> getSetting('always_show')) {
			return false;
		}
		if ($ctm -> getSetting('never_show')) {
			return false;
		}
		if (!$cpage -> hasAnyPermissions(array('create', 'view', 'edit'))) {
			return false;
		}
		return true;
	}

	function isActive($cpage) {
		return $cpage === ADD::sys() -> getAccessManager() -> getCurrentPage();
	}

	function generate() {
		$parentPage = $this->_page -> getParentPage();
		
		if($parentPage->getSetting('ConditionalTopSection.hide_topmenu')) {
			return;
		}
		$page = $this -> _page;
		$compList = $page -> getSettingList('components', ' ');
		if (in_array("ConditionalTopMenu", $compList))
			return;
		$topOptions = ADD::web() -> getContent('topMenu');
		$ppage = $page -> getParentPage();

		$cpages = $ppage -> getChildPages();

		$availPages = array();

		foreach ($cpages as $cpage) {
			if ($cpage -> hasComponent('Encounter')) {
				$cpage -> getComponent('Encounter') -> reset();
			}
			if (!$this -> isAvailable($cpage))
				continue;
			$availPages[] = $cpage;
		}

		$topOptions -> startElement("ul", array("class" => "nav nav-pills"));
		if(count($availPages)>0 && $availPages[0]->getSetting("weight")) {
            usort($availPages, function ($a, $b) {
                $aWeight = intval($a->getSetting("weight", 999));
                $bWeight = intval($b->getSetting("weight", 999));
                if ($aWeight == $bWeight) {
                    return 0;
                }
                return ($aWeight > $bWeight) ? +1 : -1;
            });
        }
		foreach ($availPages as $cpage) {
			$liAttrs = array();
			if ($this -> isActive($cpage))
				$liAttrs['class'] = 'active';

			$topOptions -> startElement("li", $liAttrs);
			$cpath = $cpage -> createMenuUri();
			$menuAttrs = $cpage -> createMenuAttrs();
			$menuAttrs['href'] = $cpath;
			$topOptions -> startElement("a", $menuAttrs);
			$menuIcon = $cpage -> createMenuIcon();
			if ($menuIcon) {
				if (!is_array($menuIcon))
					$menuIcon = array($menuIcon);
				foreach ($menuIcon as $icon) {
					$topOptions -> addTextElement("i", "", array('class' => $icon));
				}
				$topOptions -> addHtml("&nbsp;");
			}
			$menuTitle = $cpage -> createMenuLabel($cpage);

			$topOptions -> addHtml($menuTitle);
			$topOptions -> endElement("a");
			$topOptions -> endElement("li");
		}
		if($parentPage->getPermission("delete")) {
            $per = $parentPage->getEncounterRecord();
            $per->retrieve();

            $basePath = $ppage->createMenuPath();
            $eid =  $per -> getId();
            $deleteUri =  ADD_Util::uri($basePath,array("pid"=> CLE::sys()->getPatientId(),"eid"=> $eid,"t"=>ADD_Util::token(),"action"=>"delete"));
            $deleteName = "__".$parentPage->getName()."_delete_".$eid;
            $opts = array("heading"=>"Are you sure you want to delete this ".$ppage->getSetting('title')."?","text"=>"This record will be marked as deleted in ADD","uri"=>$deleteUri);
            $page->addJavascript("ADD.assign('ConfirmAction','".$deleteName."',".json_encode($opts, JSON_HEX_APOS | JSON_HEX_QUOT).")");

            $topOptions->startElement("li", array("class" => "pull-right"));
            $attrs = array("class"=>"btn btn-warning btn-mini","id"=>$deleteName,"name"=>$deleteName,"type"=>"button");
            $topOptions->addTextElement("button", "<i class='icon-remove icon-white'></i>&nbsp;Delete section", $attrs);
            $topOptions->endElement("li");
        }

		$topOptions -> endElement("ul");
		if(!$cpage->hasComponent('ConditionTopMenu')) {
		    return;
        }
		$ctm = $cpage -> getComponent('ConditionalTopMenu');

		if ($cpage -> getSetting('condition_page') != $page -> getName())
			return;
		if ($this -> isConditionMet())
			return;

		if (ADD::sys() -> hasState(ADD_System::INUSE)) {
			ADD::web() -> addWarning($ctm -> getSetting('show_inuse_message'));
			return;
		}
		ADD::web() -> addWarning($ctm -> getSetting('show_message'));
	}

	function isConditionMet() {
		return $this -> _conditionMet;
	}

	function setConditionMet($conditionMet) {
		$this -> _conditionMet = $conditionMet;
	}

	function _getConditionMetValue() {
		$page = $this -> _page;
		$condPageName = $this -> getSetting('condition_page');
		if (!$condPageName) {
			return true;
		}
		$condFieldName = $this -> getSetting('condition_field');
		$condFieldValue = $this -> getSetting('condition_value');
		$condType = $this -> getSetting('condition_type');
		$condEpisode = $this->getSetting('condition_episode');

        $sectionId = ADD::sys() -> getParam('eid');

        if(!$condEpisode) {
            $condPage = $page -> getSiblingPage($condPageName);
		}
		else {
		    $episodeGroup = CLE::svc(\ClinicLE\Episode\Resource\EpisodeServices::EPISODE_GROUP);
		    $episode = $episodeGroup->getEpisodeWithSectionId( ADD::sys() -> getParam('eid') );
            list($sectionName, $pageName) = explode(".",$condPageName);
            $sectionType = $page->getModule()->getPage($sectionName)->getSetting("encounter");
            $condSection = $episodeGroup->getSectionInEpisode($sectionType, $episode['id']);

            $condSection->retrieve();
            if(!$condSection->exists()) {
                return false;
            }
            $condPage = $page->getModule()->getPage($pageName);
            $sectionId = $condSection->getId();
		}
		if (!$condPage) {
			throw new ADD_PageNotFoundException("Page '$condPageName' with sibling '" . $page -> getName() . " not found");
		}
		if (!ADD::sys() -> getParam('eid')) {
			return false;
		}
		
		$condRecord = $condPage -> getEncounterRecord($sectionId);
        if($condEpisode) {
            CLE::info("condRecord:", $condRecord);
        }
        if (!$condRecord -> exists()) {
			
			return false;
		}
		
		$condRecord -> retrieve();

		if ($condType == "complete") {
			if ($condRecord -> getEncData('is_complete') == '1') {
				return true;
			}
			return false;
		}

		if ($condType == "verified") {
			if ($condRecord -> getEncData('is_verified') == '1') {
				return true;
			}
			return false;
		}
		$obsValue = $condRecord -> getObsValue($condFieldName);
        if (!$condFieldValue) {
			if (!ADD_Util::isEmpty($obsValue)) {
				return true;
			}
			return false;
		}
		if(!is_array($condFieldValue)) {
			$condFieldValue = array($condFieldValue);
		}
		if(!is_array($obsValue)) {
			$obsValue = array($obsValue);
		}
		else {
			$obsValue = array_keys($obsValue);
		}
		
		foreach($condFieldValue as $condFieldValueItem) {
			if (in_array($condFieldValueItem, $obsValue)) {
				return true;
			}
		}
		return false;
	}

}
