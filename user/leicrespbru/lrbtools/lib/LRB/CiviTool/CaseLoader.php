<?php
require_once("LRB/CiviTool/Entity.php");
require_once("LRB/CiviTool/Util.php");
/*
 * Core CiviLoader module which is provided by a DataFile object loaded with data items and processes them into CiviCRM entity actions
 */
class LRB_CiviTool_CaseLoader {
#utility method to load XML for a particular case given its ID


#add all activities in a specific ActivitySet to the specified case
static function run($caseType, $reverseFlag=false) {
	require_once "CRM/Case/XMLProcessor.php";
	require_once "CRM/Case/XMLProcessor/Process.php";
	require_once "CRM/Case/BAO/Case.php";
	require_once "CRM/Core/Permission.php";
	
	#load XML file for case type
	$xml = LRB_CiviTool_Util::loadCaseXMLWithType($caseType);
	if (!$xml) throw new LRB_CaseToolException("XML file for '$caseType' could not be read");
	
	
	#process XML to obtain entities
	$ents = array();
	
	$ctEnt = LRB_CiviTool_Entity::factory(array(
		LRB_CiviTool_Entity::TYPE => "CaseType",
		LRB_CiviTool_Entity::NAME => $caseType,
		LRB_CiviTool_Entity::LABEL => $caseType,
	));
	$ents[]=$ctEnt;
	foreach($xml->ActivityTypes->children() as $at) {
		$atName = (string)$at->name;
		$atEnt = LRB_CiviTool_Entity::factory(array(
			LRB_CiviTool_Entity::TYPE => "ActivityType",
			LRB_CiviTool_Entity::NAME => $atName,
			LRB_CiviTool_Entity::LABEL => $atName,
			LRB_CiviTool_Entity::PARENT => "Case"
		));
		$ents[]=$atEnt;
	}
	$action = $reverseFlag ? "delete" : "create";
	foreach($ents as $ent) {
		try {
			list($label,$name) = $ent->getLabelAndName();
			echo strtoupper($action)." $name ($label) = ".$ent->getOption('Type')."\n";	
				
			$ent->$action();
		}
		catch(LRB_CiviToolException $ex) {
			echo "CASELOADER ERROR:".$ex->getMessage()."\n";
		}
		catch(Exception $ex) {
			echo "UNKNOWN ERROR:".$ex->getMessage()."\n".$ex->getTraceAsString();
		}
	}
	
}

}

?>