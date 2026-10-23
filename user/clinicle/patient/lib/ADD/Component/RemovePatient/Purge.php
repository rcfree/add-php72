<?php

class ADD_Component_RemovePatient_Purge extends ADD_Component {
	function displayAccess() {
        $page = $this->_page;
		if (!$page->getPermission('purge')) {
			throw new ADD_AccessException("Sorry you do not have permission to purge patients.");
		}
	}	
	public function generate() {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		$adb = ADD::db('audit');
		$pdb = ADD::db('patient');
        $page = $this->_page;

		$pid = $add->getPatientId();
		list($patient,$address,$gp) = $pdb->getPatientDemographics($pid);
        if(!$patient) {
            ADD::sys()->setState('redirect', $add->config('default_path'));
            throw new \ClinicLE\Patient\Exception\InvalidPatientException("Patient with ID $pid does not exist");
        }
        $patientEvent = new \ClinicLE\Patient\Resource\PatientEvent($pid);
        CLE::sys()->getEventDispatcher()->dispatch(\ClinicLE\Patient\Resource\PatientEvents::BEFORE_PURGE_PATIENT,$patientEvent);
        $db->purgePatientEncounters($pid);
		
		$pdb->purgePatient($pid);
		$page->addMessage("Entire patient record for ".$patient['first_name']." ".$patient['last_name']." (".$patient['identifier'].") purged successfully");
		$adb->auditPatient("purge",$patient,$address,$gp);
		ADD::sys()->setState('redirect', "/");
	}
	function title(&$title) {
		$title = "Purge patient";
	}
	function breadcrumb(&$breadcrumb) {
		$breadcrumb = array("Patient","Purge existing patient");
	}
	function menuPath(&$menuPath) {
		$menuPath="patient/purge";
	}
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('purge');
	}
}