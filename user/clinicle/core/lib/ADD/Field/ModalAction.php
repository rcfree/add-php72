<?php

class ADD_Field_ModalAction extends ADD_Field_Base {
	protected $stored = false;
	protected $action;
	protected $encounterRecord;
	protected $modal;
	function getDisplayText($page,$id) {
		$this->id=$id;
		$add = ADD::sys();
		$form = $page->getComponent('Form');
		$edb = ADD::db('encounter');
		$links = array();
		$this->action = $this->getSetting('modal_action');
		$this->modal = $this->getSetting('modal_name');
		if($add->hasState('readOnly') && $this->action!='view') return;
		
		$beforeJs = $this->getSetting('modal_beforejs');
		$afterJs = $this->getSetting('modal_afterjs');
		$showExpression = $this->getSetting('show_expression');
		$this->encounterRecord = $add->getEncounterManager()->recordById($id);
		$hr = ADD::web()->newContent();
		if ($this->hasPermissionToPerformAction($page)) {
			if ($this->isRowFilteredOut()) return null;
			if (!$this->modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
			if (!$this->action) throw new ADD_FieldException("No 'modal_action' setting provided in field ".$this->getName());
			$modalEntryOpt = array(
				"action" => $this->action,
				"name" => $this->modal,
				"elementId" => $this->modal,
				"id" => $id,
				"description" => $this->getActionHeading($page),
				"lid"=>$page->getModule()->getName(),
				"fid"=>$page->getName(),
				"beforeFunctionName"=>$beforeJs,
				"afterFunctionName"=>$afterJs,
                                "pid"=>$add->getPatientId()
			);
			$json = json_encode($modalEntryOpt);
			$json = str_replace('"',"'",$json);
			$title = $this->getTitle();
			if(strlen($title)==0) $title="no title";
			$hr->addTextElement("a",$title,array("href"=>"javascript:;", "onclick"=>'modalEntry('.$json.')')); 
		}
		
		return $hr->toHtml();
	}
	function hasPermissionToPerformAction($page) {
		$action = $this->getSetting('modal_action');	
		return $page->getPermission($action);
	}
	
	function getActionHeading($page) {
		return $page->getSetting('single');
	}
	function isRowFilteredOut() {
		$this->encounterRecord->retrieve();
		if ($this->encounterRecord->getObsValue('current_status')=='2' && $this->action!='view') return true;
	}
}
?>