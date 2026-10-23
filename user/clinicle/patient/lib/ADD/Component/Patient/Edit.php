<?php

class ADD_Component_Patient_Edit extends ADD_Component {
	protected $_componentList = array("RequiredFields_Validate", "Form_File","OptionContainer","SaveButton");
	protected $validateMessage = "Sorry cannot update this patient.";	
		
	function title(&$title) {
        $page = $this->_page;

        list($patEdit,$patView) = $page->getPermissions(array('edit','view'));
	   $title = ($patView && !$patEdit ? "View ":"Edit ").$page->getSetting('title');
	}
	function breadcrumb(&$breadcrumb) {
        $page = $this->_page;

        $breadcrumb = array("Patient","Edit Demographics");
	}
	
	function displayAccess() {
		$add = ADD::sys();
        $page = $this->_page;

        if (!$page->getPermission('view') && !$page->getPermission('edit')) {
			throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to view patient demographics.");
		}
		
		if ($page->getPermission('edit')==false) {
			if ($page->getPermission('view')) {
				$add->setState('readOnly',true);
				$page->addWarning("You only have permission to view patients. You will not be able to make changes.");
			}
			else {
				throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to view or edit patient demographics.");
			}
		}
	}
	
	function submitAccess($values) {
        $page = $this->_page;

        if (!$page->getPermission('edit')){
			throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to change patient demographics data");
		}	
	}
	
	function beforePopulate() {
		$add = ADD::sys();
        $page = $this->_page;

        $pdb = ADD::db('patient');
		$adb = ADD::db('audit');
		$patientId = $add->getPatientId();
		if (!$patientId) throw new ADD_PatientException("No patient ID was provided to the Page");
		if (!$pdb->doesPatientExist($patientId,true)) throw new \ClinicLE\Patient\Exception\InvalidPatientException("The specified patient was not found in ADD (internal id:$patientId).");
		
		list($patient,$address, $gp) = $pdb->getPatientDemographics($patientId);

		if ($patient) {
			$adb->auditPatient("view",$patient,$address,$gp);
		}

		$data = $patient + ($address ? $address : array()) + ($gp ? $gp : array());
        $page->setData($data);
	}
	
	function afterGenerate() {
        $page = $this->_page;

        $patientId = ADD::sys()->getPatientId();
		$html = $page->html();
		$html->addElement('input',array('name'=>'__patient_id','type'=>'hidden','value'=>$patientId));
		if ($page->getPermission('edit')==false) {
			ADD::web()->addJavascript('$(".content-search").hide()');
		}
        $html->addElement("input",array("type"=>"hidden","value"=>"1","id"=>"ng-reload"));

        parent::afterGenerate();
	}
	
	function prepareStates() {
        $sys = ADD::sys();
		$sys->setState('new',false);
	}

	public function submit($values) {
		$add = ADD::sys();
        $page = $this->_page;
		$patientId = $add->getPatientId();
		
		$pdb = ADD::db('patient');
		$adb = ADD::db('audit');

		list($p,$a,$g) = $pdb->updatePatientDemographics($patientId,$values);
		$page->addMessage("The patient demographics was updated successfully");
		$adb->auditPatient("update",$p,$a,$g);
		
		$add->setPatientId($p['id']);
		return array('redirect'=>'patient/demographics?pid='.$p['id']);
	}
	
	function menuPath(&$menuPath) {
		$menuPath="patient/demographics";
	}
	
	function menuParams(&$menuParams) {
		$menuParams["pid"]=ADD::sys()->getPatientId();
	}
	
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('edit','view');
	}
	function beforeGenerate() {
		$add = ADD::sys();
        $page = $this->_page;
		$page->addJavascript("jQuery('#tabs-tabs a[href=\"#personal\"]').tab('show');");
		if (ADD::db('patient')->isPatientDeleted($add->getPatientId())) {
			$page->addWarning("This patient is marked as deleted so cannot be changed.");
			$add->setState('readOnly',true);
		}
	}
}
?>