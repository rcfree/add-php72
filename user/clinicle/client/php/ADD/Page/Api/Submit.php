<?php

class ADD_Page_Api_Submit extends ADD_Page_Api {
	function createMenuPath() {
		return "client/modal-submit";
	}
	
	function apiOutput($values=null) {
		$add = ADD::sys();
        $db = ADD::db('BASE');
		$pimName=$values['__pim'];
		
		$this->_pim = $add->getPageFromPim($pimName);
		
		if(!$this->_pim) throw new ADD_PageNotFoundException("Page in module '".$pimName."' not found");
		if(!isset($values['__action'])) {
			$add->setState('new',true);
			$add->setState('action','create');
		}
		
		$add->setPatientId($values[ADD_Component::PATIENT_ID]);
		
		$this->_pim->setupComponents();
		$data = $this->_pim->submitForm($values);
        $db->beginTxn();
        ADD::web()->afterSubmit($this->_pim, $values);
        if ($db->inTxn()) $db->commitTxn();
		return $data;
	}
}
