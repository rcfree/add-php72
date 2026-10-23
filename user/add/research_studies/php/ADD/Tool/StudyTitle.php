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
class ADD_Tool_StudyTitle extends ADD_Component_Content_ModalEntry {
	public function __construct(ADD_Page $page) {
		$this->_page = $page;
	}
	public function breadcrumb(&$breadcrumb) {
		$breadcrumb=array($this->getStudy());
	}
	public function title(&$title,$arrangement=null) {
		if(!$arrangement) $arrangement = $this->_page->getSetting('title')." for '%s'";
		$title=sprintf($arrangement,$this->getStudy());
	}
	
	private function _getParentRecord() {
		if($this->_parentRecord) return $this->_parentRecord;
		$parent = ADD::sys()->getEncounterManager()->recordById(ADD::sys()->getParam('eid'));
		$parent->retrieve();
		$gparent = $parent->getParent();
		$gparent->retrieve();
		return $gparent;
	}
	private $_study;
	private $_parentRecord;
	
	function getStudy() {
		if ($this->_study) return $this->_study;
		if($this->_page->getName()!="action") {
			$detailsPage = $this->_page->getSiblingPage('sdetails');
			$add = ADD::sys();
			$detailsEr = $detailsPage->getEncounterRecord($add->getParam('eid'));
			$detailsEr->retrieve();
			$this->_study = $detailsEr->getObsText("study",$detailsPage); 
		}
		else {
			$em = ADD::sys()->getEncounterManager();
			$gparent = $this->_getParentRecord();
			$sibling = $em->child($gparent,"res-study-details");
			$sibling->retrieve();

			$page = $this->_page->getParent()->getSiblingPage("sdetails");
			$this->_study = $sibling->getObsText("study",$page);
		}
		return $this->_study;
	}
	function getParentId() {
		return $this->_getParentRecord()->getId();
	}
}
