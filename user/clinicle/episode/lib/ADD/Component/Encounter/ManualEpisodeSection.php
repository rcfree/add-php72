<?php
use Clinicle\Util\Redirector;

class ADD_Component_Encounter_ManualEpisodeSection extends ADD_Component_Encounter_EpisodeSection {
	public function menu($options) {
		parent::menu($options);
		$page = $this->getPage();
		$currEpisode = $page->getParent()->getComponent('Encounter')->getCurrentEpisode();
		if($options->countElements()>0) return;
		if(!$this->getPage()->getPermission('create')) return;
		$href = ADD_Util::uri($page->createMenuPath(),array("pid"=>ADD::sys()->getPatientId(),"epid"=>$currEpisode['id'],"a"=>"create","t"=>ADD::db('token')->generate()));
		//$options->startElement("li",array("class"=>"episode-button"));
		$options->addTextElement("li","<i class='icon-plus icon-white'></i>&nbsp;Add ".$page->getSetting('title'),array("ng-click"=>"gotoPage('".$href."')","class"=>"btn btn-small btn-success episode-button"));
		//$options->endElement("li");
	}
	
	public function generate() {
		$add = CLE::sys();
		$epid = $add->getParam('epid');
	    $db = ADD::db('BASE');
		$page = $this->getPage();

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

		if (!ADD::db('token')->register()) {
			$page -> addError("You have already added a '" . $page -> getSetting('title') . "'. To ensure data integrity you can only add one at a time.");
			return;
		}

        $events = $page->getEvents();
        
        $event = new ADD_EpisodeSectionEvent();
        $event->setEpisodeId($epid);
        $event->setPatientId(ADD::sys()->getPatientId());
        
        $eventNames = array("episodeSection.beforeCreate","episodeSection.create","episodeSection.afterCreate");
        $propStopped = $events->dispatchMultiple($eventNames,$event);
        if($propStopped) {
            return;
        }
        
		$redirector = new Redirector();
        $section = $event->getSection();
        $redirector->redirectUsingSetting($page,$event->getPatientId(),$section->getId(),$page->getSetting('title')." visit");
	}
	
	public function menuSectionLabel($sectionEr) {
		$sectionLabel = parent::menuSectionLabel($sectionEr);
		$sectionEr->retrieve();
		$event = new ADD_EpisodeMenuSectionEvent();
        $event->setEpisodeId($sectionEr->getEncData("episode_id"));
        $event->setPatientId($sectionEr->getPatient());
		$event->setSection($sectionEr);
		$event->setLabel($sectionLabel);
		$page = $this->getPage();
		
        $events = $page->getEvents();
        $events->dispatch("episodeSection.menuSectionLabel",$event);
		return $event->getLabel();
	}
}
