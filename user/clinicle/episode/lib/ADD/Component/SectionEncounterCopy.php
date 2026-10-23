<?php
class ADD_Component_SectionEncounterCopy extends ADD_Component {
    public function setup() {
        $events = $this->getPage()->getEvents();
        
        if(!$this->getPage()->hasComponent("CopyEpisodeSectionSupport")) {
            throw new ADD_ComponentException("CopyEpisodeSectionSupport component should be included in page");
        }
        $events->addListener("copyEpisodeSection.beforeCopy",array($this,"onBeforeCopy"));
        $events->addListener("copyEpisodeSection.copy",array($this,"onCopy"));
    }
    public function onBeforeCopy($event) {
        $em = ADD::sys()->getEncounterManager();
        $page = $this->_page;
        $db = ADD::db('encounter');
		$epiGroup = ADD::sys()->getService(\ClinicLE\Episode\Resource\EpisodeServices::EPISODE_GROUP);

        if(!$epiGroup->hasSectionInEpisode($this->assertSetting("copy-section-type"), $event->getEpisodeId
        ())) {
            $event->setSourceSection(null);
            return;
        }

        $sourceSection = $epiGroup->getSectionInEpisode($this->assertSetting("copy-section-type"), $event->getEpisodeId());

        if(!$sourceSection->exists()) {
            throw new Exception("Source section does not exist");
        }
        $event->setSourceSection($sourceSection);
    }

    public function onCopy($event) {
        $em = ADD::sys()->getEncounterManager();
        $page = $this->getPage();
        $sourceEncType = $this->assertSetting("source-encounter-type");
        $targetEncType = $this->assertSetting("target-encounter-type");
        $sourceBadge = $this->getSetting("source-badge");
        
        $records = $em->children($event->getSourceSection(),$sourceEncType);
        $targetSection = $event->getTargetSection();
        
        $count = 0;
        foreach($records as $record) {
            $record->retrieve();
            if($sourceBadge && $record->getEncData("badge")!=$sourceBadge) {
                continue;
            }
            $newComorb = $em->newChild($targetSection,$targetEncType);
            $newComorb->store($record->getObsValues());
            $count++;
        }
        ADD::web()->addMessage("Copied ".$count." existing ".$this->getSetting("source-encounter-label")." ".ADD_Util::pluralize($count,"record")." to ".$page->getSetting("title"));
    }
}