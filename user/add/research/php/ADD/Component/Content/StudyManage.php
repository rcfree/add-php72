<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StudyModalEntry
 *
 * @author rcf8
 */

class ADD_Component_Content_StudyManage extends ADD_Component {
	function beforeGenerate() { 
		$add = ADD::sys();
		if($submitPage->getName()!= "list") return;
		$id = $add->getParam('eid');
		$em = $add->getEncounterManager();
		$er = $em->recordById($id);
		$er->retrieve();
		if(!$er->exists()) throw new Exception("Study with ID '$id' does not exist");
		
		$er = $em->record("res-manage",$add->getPatientId());
		$er->setId($id);
		$er->store();
		$submitPage->addMessage("Added study record");
	}
	
}
