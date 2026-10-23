<?php
class LRB_CiviTool_Util {
static function loadCaseXMLWithType($caseType) {
	require_once "CRM/Case/XMLProcessor.php";
	require_once "CRM/Case/XMLProcessor/Process.php";
	require_once "CRM/Case/BAO/Case.php";
	require_once "CRM/Core/Permission.php";
	
	#load XML file for case type
        $xp = new CRM_Case_XMLProcessor();
	$xml = $xp->retrieve($caseType);
	return $xml;
}

}
?>