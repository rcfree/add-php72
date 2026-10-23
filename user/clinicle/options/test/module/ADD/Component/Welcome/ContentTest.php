<?php
class ADD_Component_WelcomeTest extends ADD_Test_EncounterBase {
	public function testWelcomeContentWhenNoPatient() {
		$content = ADD::web()->createOutput(array("method"=>"GET","path"=>"home","values"=>array()));
		$this->assertHasTextContent("",'sideCommon');
		$this->assertHasTextContent("",'options');
		$this->assertHasTextContent("",'main');
		$this->assertHasTextContent("",'search');
		
		$this->assertHasNoErrors();
	}

}