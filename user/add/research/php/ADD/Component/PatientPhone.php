<?php
class ADD_Component_PatientPhone extends ADD_Component {
	function afterPopulate() {
		if (!ADD::sys()->hasState("new")) return;
        $page = $this->_page;
        $form = $this->_page->getComponent('Form');
		$phone = $form->getField('phone_number');
        if(!ADD_Util::isEmpty($phone->getValue())) return;
        list($p,$a,$g) = ADD::db('patient')->getPatientDemographics(ADD::sys()->getPatientId());
		$homePhone = ADD_Util::val('home_phone',$a);
		if(!$homePhone) {
			ADD::web()->addWarning("Phone number in patient demographics is empty so was not copied");
			return;
		}
		$phone->setValue($homePhone);
		ADD::web()->addMessage("Phone number was copied from patient demographics information");
    }

} 