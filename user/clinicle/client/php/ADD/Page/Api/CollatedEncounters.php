<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Page_Api_CollatedEncounters extends ADD_Page_Api_Encounters {
	function isOnlyLabelOutput() {
		return false;
	}
	function apiOutput() {
		$add = ADD::sys();
		$log = ADD::log($this);
		$pimName = $add->assertParam('pim');
		$temp = explode(":",$pimName);
		$pim = $add->getPageInModule($temp[0],$temp[1]);
		//if(!$pim->getPermission("view")) throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to view ".$pim->getSetting('single')."s");
		$fileName = $pim->getFilePath()."/collate/".$pim->assertSetting('collate_file');
		if(!file_exists($fileName)) throw new Exception("Collation file '$fileName' does not exist");
		$collateData = file_get_contents($fileName);
		$collateConfig = json_decode($collateData,true);
		
		$rows = array();
		foreach($collateConfig as $collatePim => $collateInfo) {
			$add->setParam('pim',$collatePim);
			$collatePimPage = $add->getPageFromPim($collatePim);
			$collatePimPage->setPermission("view",true);
			try {
				$collateRows = $this->mapRows(parent::apiOutput(),$collateInfo, $collatePim);
				$rows = array_merge($rows,$collateRows);
			}
			catch(Exception $ex) {
				if(!preg_match("/Module .+ does not exist/",$ex->getMessage())) {
					ADD::web()->addWarning("Error during retrieval of records: ".$ex->getMessage());
				}
			}	
		}
		
		$data['data']=$rows;
		$data['total']=count($rows);
		$data['first']=count($rows)==0 ? 0 : 1;
		$data['last']=count($rows);
		return $data;
	}
	
	function mapRows($inputRows,$collateInfo,$pim) {
		$outputRows = array();
		$collateMap = $collateInfo['map'];
		$collateConsts = $collateInfo['constants'];
		$collateLookups = ADD_Util::val('lookups',$collateInfo,array());
		if($collateLookups) {
			$pimPage = ADD::sys()->getPageFromPim($pim);
			$pimParent = $pimPage->getParent();
		}
		$em = ADD::sys()->getEncounterManager();
		foreach($inputRows['data'] as $inputRow) {
			$outputRow = array("id"=>$inputRow['id'],"parent_encounter_id"=>$inputRow['parent_encounter_id'],"date_created"=>$inputRow['date_created'],"badge"=>array("label"=>$inputRow['badge']));
			foreach($collateMap as $targetField => $sourceField) {
				if(is_numeric($targetField)) {
					$targetField = $sourceField;
				}
				$outputRow[$targetField] = ADD_Util::val($sourceField,$inputRow);
			}
			foreach($collateConsts as $targetField => $targetValue) {
				$outputRow[$targetField]=array("label"=>$targetValue,"value"=>$targetValue);
			}
			foreach($collateLookups as $targetField => $lookupField) {
				$pimRecord = $pimParent->getEncounterRecord($inputRow['parent_encounter_id']);
				list($siblingPageName,$siblingFieldName) = explode(".",$lookupField);
				$siblingPage = $pimParent->getChildPage($siblingPageName);
				$siblingEnc = $siblingPage->getSetting('encounter');
				$siblingRecord = $em->child($pimRecord,$siblingEnc);
				$siblingRecord->retrieve();
				$targetValue = $siblingRecord->getObsValue($siblingFieldName);
				$outputRow[$targetField]=array("label"=>$targetValue,"value"=>$targetValue);
			}
			$outputRows[]=$outputRow;
		}
		return $outputRows;
	}
}