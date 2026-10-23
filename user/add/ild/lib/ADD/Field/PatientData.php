<?php

class ADD_Field_PatientData extends ADD_Field_Decorator {
	static $patient;
	static $pid;
	
	function getDecoratorType() {
		return "ADD_Field_".$this->getSetting('data_type','Text');
	}
	
	function populateValue($values, $page, $ignoreNew=false) {
			
		$add = ADD::sys();
		if (!$add->hasState('new')) {
			return parent::populateValue($values,$page,$ignoreNew);
		}
		
		$pid = ADD::sys()->getPatientId();
		$patientField = $this->assertSetting('field');
		if(!self::$patient || self::$pid!=$pid) {
			$patient = ADD::db('patient')->getPatient($pid);
			self::$patient = $patient;
			self::$pid = $pid;
		}
		$patientItem = self::$patient[$patientField];
		
		$this->setValue($patientItem);
	}
	 
	
}