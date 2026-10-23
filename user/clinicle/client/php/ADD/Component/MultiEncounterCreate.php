<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Component_MultiEncounterCreate extends ADD_Component {
	function submit($values) {
		$page = $this->_page;
		$form = $page->getComponent('Form');
			
		$detailEncounters=array();
		
		$commonObs = $this->_extractCommonObs($form,$values);
		$multiEncounters = $this->_createMultiEncounters($form,$values,$commonObs);
		
		$em = ADD::sys()->getEncounterManager();
		
		$multiPageName = $page->assertSetting('multi_page');
		$multiPage = ADD::sys()->getPageFromPim($multiPageName);
		if(!$multiPage) throw new ADD_PageNotFoundException("Multi page '".$multiPageName."' was not found for the MultiEncounterCreate component");
		
		$topPages = $multiPage->getModule()->getTopPages();
		$domainPage = $topPages[0];
		$domain = $domainPage->getEncounterRecord();
		if(!$domain->exists()) $domain->store();
		
		$per = $em->newSection($domain->getPatient(),$multiPage->getParent()->getSetting('encounter'));
		$per->store();
		
		$detailPageName = $page->getSetting('detail_page');
			
		if($detailPageName) {
			$detailPage = ADD::sys()->getPageFromPim($detailPageName);
			$detailEncType = $detailPage->getSetting('encounter');
		
			$detailEncounter = $this->_createDetailEncounter($form,$values);
			$der = $em->newChild($per,$detailEncType);
			$der->store($detailEncounter);
		}
		
		$multiEncType = $multiPage->getSetting('encounter');

		$id = null;
		foreach($multiEncounters as $multiEncounter) {
			$cer = $em->newChild($per,$multiEncType);
			$cer->store($multiEncounter);
			$id = $cer->getId();
		}
		ADD::web()->addMessage("Added ".count($multiEncounters)." ".ADD_Util::pluralize($multiEncounters,$multiPage->getSetting('single')));
		return array("id"=>$id, "parent_encounter_id"=>$per->getId());
	}
	
	function _extractCommonObs($form,$values) {
		$commonObs = array();
		
		$form = $this->_page->getComponent('Form');
		$fields = $form->getFields();
		
		foreach($fields as $field) {
			$multi_field = $field->getSetting('multi_field');
			if(!$multi_field) continue;

			if($field->getSetting('multi_no')) continue;
			$commonObs[$multi_field]=ADD_Util::val($field->getName(),$values);
			continue;
		}
		return $commonObs;
	}
	function _createMultiEncounters($form,$values,$commonObs) {
		$detailEncounters=array();
		
		$form = $this->_page->getComponent('Form');
		$fields = $form->getFields();
		
		foreach($fields as $field) {
			$multi_field = $field->getSetting('multi_field');
			if(!$multi_field) continue;

			$multi_no = $field->getSetting('multi_no');
			if(!$multi_no) continue;
			$multiConstants = $field->getSetting('multi_constant');
			
			if($multiConstants) {
				foreach($multiConstants as $multiConstant) {
					list($ofcName,$ofcValue) = explode(":",$multiConstant);
					$detailEncounters[$multi_no][$ofcName]=$ofcValue;
				}
			}
			if(!isset($detailEncounters[$multi_no])) $detailEncounters[$multi_no]=array();
			$detailEncounters[$multi_no][$multi_field]=ADD_Util::val($field->getName(),$values);
		}
		$combinedEncounters=array();
		foreach($detailEncounters as $detailEncounter) {
			$combinedEncounters[] = $detailEncounter + $commonObs;
		}
		return $combinedEncounters;
	}
	function _createDetailEncounter($form,$values) {
		$detailEncounter=array();
		
		$form = $this->_page->getComponent('Form');
		$fields = $form->getFields();
		
		foreach($fields as $field) {
			$detail_field = $field->getSetting('detail_field');
			if(!$detail_field) continue;

			$detailConstant = $field->getSetting('detail_constant');
			
			if($detailConstant) {
				$detailEncounter[$detail_field]=$detailConstant;
				continue;
			}
			
			$detailEncounter[$detail_field]=ADD_Util::val($field->getName(),$values);
		}
		if(count($detailEncounter)==0) return null;
		return $detailEncounter;
	}
	function expectedPermissions(&$expPerms) {
		$expPerms[]="create";
	}
}
?>
