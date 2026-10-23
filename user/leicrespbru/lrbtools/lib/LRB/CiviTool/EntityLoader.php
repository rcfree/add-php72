<?php

/*
 * Core CiviLoader module which is provided by a DataFile object loaded with data items and processes them into CiviCRM entity actions
 */
class LRB_CiviTool_EntityLoader {
	
	private static $opposites = array(
		'create' => 'delete'
	);
	
	/*
	 * Read through the items in the DataFile object and process them using the CiviLoader entities factory
	 */
	static function run($dataFile,$reverseFlag=false) {
		#loop through each line and create an Entity object for each based on the options in the line
		$dataFile->load();
		$dataItems = $dataFile->getDataItems();
		if ($reverseFlag) $dataItems = array_reverse($dataItems);
		foreach($dataItems as $item) {
			$entity = LRB_CiviTool_Entity::factory($item);
			#determine entity action (depends on disabled or not)
			$action = LRB_CiviTool_EntityLoader::determineAction($entity,$reverseFlag);
			list($label,$name) = $entity->getLabelAndName();
			echo strtoupper($action)." $name ($label) = ".$entity->getOption('Type')."\n";
			#run entity action on the line and deal with errors
			try {
				if (!$action) throw new LRB_CiviToolException("No 'action' provided for field ".$entity->getOption('Name'));
				$entity->$action();
			}
			catch(LRB_CiviToolException $ex) {
				echo "CIVILOADER ERROR:".$ex->getMessage()."\n";
			}
			catch(Exception $ex) {
				echo "UNKNOWN ERROR:".$ex->getMessage()."\n".$ex->getTraceAsString();
			}
		}
	}

	/*
	 * Determine action from entity. This also depends on whether disableFlag is true (e.g. create becomes delete)
	 */
	function determineAction($entity,$reverseFlag) {
		$action = $entity->getOption(LRB_CiviTool_Entity::ACTION);
		if ($reverseFlag) {
			if ($action=='create') return 'delete';
		}
		return $action;
	}

}

