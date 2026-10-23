<?php

class ADD_Component_Content_ModalEntry extends ADD_Component {
	protected $_componentList = array('PatientCheck','Breadcrumb','Encounter_Single_Modal', 'Form_DB','OptionContainer','RequiredFields_Validate_Modal','MenuItem_Multiple','AccessControl');
	
	function title(&$title) {
        $page = $this->_page;
		$title = $page->getSetting('title');
	}
	
	public function submit($values) {
		$add = ADD::sys();
        $page = $this->_page;
		$form = $page->getComponent('Form');
		
		$patientId = $add->getPatientId();
		$parentIdent = isset($values[ADD_Component::PARENT_ID]) ? $values[ADD_Component::PARENT_ID] : null;
		foreach($form->getFields() as $field) {
			if (is_subclass_of($field,"ADD_Field_Submit")) {
				if ($field->isSubmittedField($values)) $field->submitValues($values,$page);
			}
		}
		return array('redirect'=>ADD_Util::uri($page->createMenuPath(),$add->getParams(),true));
	}
	
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('create','edit','view','delete');
	}
	
    function beforeSubmit(&$values) {
        $page = $this->_page;
        if (!ADD::db('token')->register($values)) {
            $page->addWarning("You have already submitted this page.");
            ADD::sys()->setState('cancel',true);
            return;
        }
    }
}
?>