<?php
class ADD_Component_Encounter_EpisodeSection extends ADD_Component_Encounter_RepeatSection {
	public function menu($options) {
		$add = ADD::sys();
		$page = $this->getPage();
		$availPages = array();
		foreach($page->getChildPages() as $childPage) {
			if($page->hasAnyPermissions(array('create','view','edit'))) {
				$availPages[]=$page;
			}
		}
		if(count($availPages)==0) {
			return;
		}
		$episode = $page->getParentPage()->getComponent('Encounter')->getCurrentEpisode();
		$section = ADD::db('BASE')->fetchFirst("select s.* from section s left join section_type st on s.section_type_id = st.id where st.name=? and s.episode_id=? and s.is_deleted!=1",array($page->getSetting('encounter'),$episode['id']));
		$sectionId = $section['id'];
		
		$sectionRecord = $add->getEncounterManager()->sectionById($sectionId);

		if(!$section) return;
        if (ADD_Util::val("is_deleted",$section)==1) return;

		$firstPageUri=null;
		foreach($page->getChildPages() as $childPage) {
			if($firstPageUri) {
				break;
			}
			if($childPage->getSetting("no-menu")) continue;
			if($childPage->hasComponent("ConditionalTopMenu") && !$childPage->getComponent("ConditionalTopMenu")->isAvailable($childPage)) {	
				continue;
			}
			$firstPageUri = ADD_Util::uri($childPage->createMenuPath(),array("pid"=>$add->getPatientId(),"eid"=>$sectionId));
		}
		
		$options->startElement("li");
		//$options->startElement("ul",array("class"=>"nav episode-options"));
		$this->_populateFieldAndPattern();
		
		$liAttrs = array("class"=>"episode-button");

		
		//$options->startElement("li",$liAttrs);
		
		$aAttrs = array("href"=>"javascript:;", "ng-click"=>"gotoPage('$firstPageUri')","class"=>"btn btn-small 
		episode-button");
        if($sectionId==ADD::sys()->getParam('eid')) {
            $aAttrs['class'].=" active";
        }
		if(!$firstPageUri) {
			$options->addTextElement("p","No access to ".$this->menuSectionLabel($sectionRecord),array("class"=>"text-warning"));
		}
		else {
			$options->addTextElement("li",$this->menuSectionLabel($sectionRecord),$aAttrs);
		}
		//$options->endElement("li");
		
		//$options->endElement("ul");
		//$options->endElement("li");
	}
	
	function title(&$title) {
		$title = $this->_page->getSetting('title');
	}
	
	public function _getChildPageAndField($page, $pageBits) {
		if($pageBits[0]=="episode") {
			$labelPage = $page->getModule()->getPage($pageBits[1]);
			$labelField = $pageBits[2];
		}
		else {
			$labelPage = $page -> getChildPage($pageBits[0]);
			if (!$labelPage) throw new ADD_PageException("Unable to get child page '{$pageBits[0]}' of '".$page->getName()."'");
			$labelField = $pageBits[1];
		}
			
		return array($labelPage,$labelField);
	}
}