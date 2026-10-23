<?php



class ADD_Page_Api_Holding extends ADD_Page_Api  {
	public function apiOutput() {
		$add = ADD::sys();
		$context = $add->getAccessManager()->getContext();
		$data = array();
		
		$pdb = ADD::db('patient');
		$snumber = $add->assertParam('identifier');
		$pim = $add->assertParam('pim');
		$page = $add->getPageFromPim($pim);
		if(!$page) throw new ADD_PageNotFoundException("Page '$pim' not found for use by 'holding' module");
		if(!$page->getPermission('move')) throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to move this record out of holding");
		$encId = $add->assertParam('encounter_id');
		$updateField = $add->getParam('update_field');
		$updateObs=array();
		if($updateField) {
			$updateObs = array($updateField=>$add->assertParam('update_value'));
		}
		$holdingService = new ADD_Data_HoldingService();
		
		$patient = $pdb->getPatientWithIdentifier($snumber);
		
		if(!$patient) {
			$patient = $holdingService->createPatientUsingIdentifier($snumber);
		}
			 
		$holdingService->moveRelatedRecordsToPatient($patient,$encId,$pim,$updateObs);
		return $data;
	}
	
}

