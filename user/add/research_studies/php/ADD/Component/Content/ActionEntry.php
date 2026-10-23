<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ActionEntry
 *
 * @author rcf8
 */
class ADD_Component_Content_ActionEntry extends ADD_Component {
	function getComponentList() {
		$cl = array(
			'PatientCheck',
			'Encounter_SingleAction', 
            'Autosave',
			'Form_DB',
			'AccessControl',
			'OptionContainer',
			'RecalculateButton',
			'SaveButton',
			'RequiredFields_Notify',
			'ConfirmSave',
            'HistoryOption',
		);
		return $cl;
	}
	protected $_action;
	protected $_actionPage;
	function setup() {
		$this->_action = ADD::sys()->getParam('a');
		$this->_page->setSetting('encounter',"res-action-".$this->_action);
	}
	function setStudyTitle($st) {
		$this->_st = $st;
	}
	function setPage(ADD_Page $page) {
		$this->_st = new ADD_Tool_StudyTitle($page);
		parent::setPage($page);
	}
	function beforeGenerate() {
		ADD::web()->getContent()->addElement("input",array("type"=>"hidden","name"=>ADD_Component::ACTION,"value"=>ADD::sys()->getParam('a')));
	}
	
	function afterGenerate() {
		$em = ADD::sys()->getEncounterManager();
        $gparentId = $this->_st->getParentId();
		$returnLink = ADD_Util::uri("research/study/actions",array("eid"=>$gparentId,"pid"=>ADD::sys()->getPatientId()));
		ADD::web()->getContent()->addBeforeElement('opt_end',array('tag'=>'a','text'=>"Return to '".$this->_st->getStudy()."' actions",'attrs'=>array("href"=>$returnLink,"class"=>"btn"),'type'=>ADD_Content_Html::TEXT));
	}
	
	function title(&$title) {
		$ddb = ADD::db('dictionary');
		$alDict = $ddb->getDictionaryByName('actionlist');
		$action = ADD::db('dictionary')->getDictionaryItemWithValue($alDict,$this->_action);
		$actionPage = ADD::sys()->getPageInModule("research_action",$this->_action);
		if(!$action) throw new ADD_DictionaryItemNotFoundException("Action '".$this->_action."' has not been defined in the system");
		
		$this->_st->title($title,"Study '%s' action: ".ADD_Util::val("title",$action," untitled"));
	}
	function breadcrumb(&$breadcrumb) {
		$this->_st->breadcrumb($breadcrumb);
	}
	function expectedPermissions(&$expPerms) {
        $page = $this->_page;
		$expPerms = $expPerms + array('create','edit','view');
	}
	
	function menuPath(&$menuPath) {
		$menuPath="research/action";
	}
	function menuParams(&$menuParams) {
		$add = ADD::sys();
		$menuParams['pid']=$add->getPatientId();
		//$menuParams['eid']=$per->getId();
	}
}
