<?php


class ADD_Component_Content_PublicEntry extends ADD_Component {
	private $_submitted=false;
	public function getComponentList() {
		return array("RequiredFields_Validate","OptionContainer","SaveButton");
	}
	public function menuPath(&$menuPath) {
		$menuPath = "ild/referral";
	}
	public function title(&$title) {
		$title="ILD Referral";
	}
	
	public function submit($values) {
		$page = $this->_page;
		$holdingService = new ADD_Data_HoldingService();
		$patientService = new ADD_Data_PatientService();
		$patient = $patientService->retrievePatientWithSpecialIdentifier($this->_page->assertSetting('specialIdentifier'));
		$holdingService->addReferralRecordToPatient($patient,$page->getSetting('encounter'),$values);
		$this->_submitted=true;
		$page->setData($values);
	}
	
	public function beforePopulate() {
		if(!$this->_submitted) {
			ADD::sys()->setState('new',true);
		}	
	}
		
	public function beforeGenerate() {
		if(!$this->_submitted) {
			ADD::web()->getContent()->addTextElement("div",
				"Please enter as much information as you can then click 'Save Data'. <br/>You may be contacted to confirm specific details in future.",
				array("class"=>"alert alert-info")
			);
			return;
		}
		ADD::sys()->setState('readOnly',true);
		ADD::web()->addMessage("Thank you for your ILD referral. We may contact you in the near future");
	}
	public function expectedPermissions(&$expPerms) {
		$expPerms=array("view","delete","move");
	}
}
?>