<?php

class ADD_Component_RemovePatient_Delete extends ADD_Component {
	function displayAccess() {
        $page = $this->_page;
		if (!$page->getPermission('delete')) {
			throw new ADD_AccessException("Sorry you do not have permission to delete patients.");
		}
	}
	public function generate() {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		$adb = ADD::db('audit');
		$pdb = ADD::db('patient');
        $page = $this->_page;
		$pid = $add->getPatientId();

        $patientEvent = new \ClinicLE\Patient\Resource\PatientEvent($pid);
        CLE::sys()->getEventDispatcher()->dispatch(\ClinicLE\Patient\Resource\PatientEvents::BEFORE_DELETE_PATIENT,$patientEvent);

        $deleteList = $db->deletePatientEncounters($pid);
		foreach($deleteList as $deleteItem) {
			$page->addMessage("{$deleteItem[0]} record deleted successfully");
			$adb->auditEncounter("delete",$deleteItem[1]);
		}
		
		$patient = $pdb->deletePatient($pid);
		$page->addMessage("Entire patient record for ".$patient['first_name']." ".$patient['last_name']." (".$patient['identifier'].") deleted successfully");
		$adb->auditPatient("delete",$patient);
        ADD::sys()->setState('redirect', "/");
	}
	function title(&$title) {
		$title = "Delete patient";
	}
	function breadcrumb(&$breadcrumb) {
		$breadcrumb = array("Patient","Delete existing patient");
	}
	function menuPath(&$menuPath) {
		$menuPath="patient/delete";
	}
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('delete');
	}
}