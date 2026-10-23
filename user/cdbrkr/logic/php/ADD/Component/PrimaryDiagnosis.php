<?php

class ADD_Component_PrimaryDiagnosis extends ADD_Component {
	public function beforeSubmit(&$values) {
		$primDiag = ADD_Util::val("primary_diagnosis",$values);
		
		if($primDiag!=1) return;
		$page = $this->getPage();
		$ppage = $page->getParentPage();
		$per = $ppage->getEncounterRecord($page->getComponent('Form')->getUncleanValue('__parent'));
		$em = ADD::sys()->getEncounterManager();
		$comorbs = $em->children($per,$page->getSetting('encounter'));
		foreach($comorbs as $comorb) {
			$comorb->retrieve();
			if($comorb->getObsValue("primary_diagnosis")!=1) continue;
			ADD::log($this)->info("set primary diagnosis to 0:".$comorb->getObsValue("icd10_code").print_r($comorb,1));
			
			$comorb->setObsValue("primary_diagnosis","0");
			ADD::log($this)->info("set primary diagnosis to 0:".$comorb->getObsValue("icd10_code").print_r($comorb,1));
			
			$comorb->store();
			
		}
		//ADD::web()->addWarning("comorbs:<pre>".print_r($comorbs,1)."</pre>");
	}
}

