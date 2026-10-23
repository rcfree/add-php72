<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CopyActiveComorb
 *
 * @author rcf8
 */
class ADD_Component_CopyActiveComorb extends ADD_Component {
    function afterGenerate() {
        $add = ADD::sys();
        $page = $this->getPage();
        $history = $page->getParent()->getEncounterRecord();
        
        $em = ADD::sys()->getEncounterManager();
        $visits = $em->children($history,"history-visit");
        if (count($visits)<=1) {
            $page->addMessage("There are no previous history records (no active comorbidities were copied to this record)");
            return; 
        }
        $currVisit = $visits[count($visits)-1];
        $latestVisit = $visits[count($visits)-2];
        
        //$per = $page->getParent()->getEncounterRecord();

        $copiedCount=0;
        $comorbs = $em->children($latestVisit,"history-visit-comorb");
        foreach($comorbs as $comorb) {
            $comorb->retrieve();

            if ($comorb->getObsValue('current_status')!=1) continue;
            $newComorb = $em->newChild($currVisit, "history-visit-comorb");
            $newComorb->store($comorb->getObsValues());
            $copiedCount++;
        }
        $page->addMessage("Copied ".$copiedCount." active ".ADD_Util::pluralize($copiedCount,"comorbidity","comorbidities")." from previous history record.");

    }
}
