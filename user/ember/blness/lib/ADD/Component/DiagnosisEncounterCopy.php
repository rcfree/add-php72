<?php
class ADD_Component_DiagnosisEncounterCopy extends ADD_Component_SectionEncounterCopy {
    private $obsValues = array();
    
    public function onBeforeCopy($event) {
        parent::onBeforeCopy($event);
        $em = ADD::sys()->getEncounterManager();
        $sourceSection = $event->getSourceSection();
        $page = $this->getPage();

        $noDiagnosis = false;
        if(!$sourceSection) {
            $noDiagnosis = true;
        }
        else {
            $sourceDiagnosis = $em->child($sourceSection, $this->assertSetting("source-diagnosis-encounter"));
            if (!$sourceDiagnosis->exists()) {
                $noDiagnosis = true;
            }
        }

        if($noDiagnosis===true) {
            ADD::web()->addWarning("Unable to add ".$page->getSetting('title')." visit to this episode. A diagnosis has not been set in the previous visit in this episode.");
            $event->stopPropagation();
            return;
        }
        
        $obsValues = array();
       
        $sourceDiagnosis->retrieve();
        foreach(explode(",",$this->getSetting("copy-observations")) as $obsName) {
            $obsValue = $sourceDiagnosis->getObsValue($obsName);
            if(!$obsValue && $obsName!="asthma_severity") {
                ADD::web()->addWarning("Unable to add ".$page->getSetting('title')." visit to this episode. ".ADD_Util::nameToLabel($obsName)." has not been set in the previous visit of this episode.");
                $event->stopPropagation();
                return;
            }
            
            $obsValues[$obsName]=$obsValue;
        }
        
        $this->obsValues = $obsValues;
        
        
    }

    public function onCopy($event) {
        parent::onCopy($event);
        $em = ADD::sys()->getEncounterManager();
        $targetSection = $event->getTargetSection();
        
        $pid = ADD::sys()->getPatientId();
        
        $ddiagnosis = $em->newChild($targetSection,$this->getSetting("target-diagnosis-encounter"));
        
        $ddiagnosis->store($this->obsValues);
        $ex = new Exception();
        ADD::web()->addMessage("Copied primary diagnosis to ".$this->getPage()->getSetting("title"));
    }
}