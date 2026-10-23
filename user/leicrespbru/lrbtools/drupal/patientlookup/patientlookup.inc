<?php 
set_include_path(get_include_path().":".getenv('LRB_PATH')."/lib");
require_once("LRB/PatientLookup.php");
global $module;
$module=array("module"=>"LivePMI");
function _patient_lookup_retrieve($id) {
	$pmi = _get_pmi();
	$record = $pmi->get_patient_by_id($id);
	return $record;
}

function _patient_lookup_index() {
	$pmi = _get_pmi();
	$records = $pmi->get_patient_records($_GET);
	return $records;
}

function _get_pmi() {
	$path = drupal_get_path('module','patientlookup')."/patientlookup.info";
	$info = drupal_parse_info_file($path);
	return LRB_PatientLookup::factory($info['PatientLookup']);
}

/**
* Access callback for the patient resource.
*
* @param string $op
*  The operation that's going to be performed.
* @param array $args
*  The arguments that will be passed to the callback.
* @return bool
*  Whether access is given or not.
*/

function _patient_lookup_access($op, $args) {
	global $user;
	$access = FALSE;

	switch ($op) {
		case 'view':
			$access = user_access('lookup patients');
			break;
	}
	
	if ($access==FALSE) {
		throw new Exception("Access to patient lookup is not available. You may need to log in again.");
	}
	return $access;
}
?>
