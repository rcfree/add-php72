<?php
class LRB_PatientLookup {

	static function get_value($key,$dict) {
	if (isset($dict[$key])) {
		return $dict[$key];
	}
	else {
		return null;
	}
}

	static function factory($config=null) {
	    if (!isset($config['module'])) throw new Exception("PatientLookup 'module' not provided");    
	    $name = $config['module'];
		$module_name = "LRB_PatientLookup_$name";
		$module_path=str_replace("_","/",$module_name).".php";
        
        if (!class_exists($module_name)) throw new Exception("PatientLookup module '$name' does not exist");
        
		$module = new $module_name($config);
		return $module;
	}
    
	public function get_patient_by_id($id) {
		return $this->get_patient_record_by_id($id);
	}
	
	private function convert_records_to_table($records) {
		$rows = array();
		
		#add labels for table using the column labels in get_column_fields
		$head = array("Patient ID");
		foreach ($this->get_column_fields() as $label => $field) {
		
			array_push($head,$label);
		}
		array_push($rows,$head);
		
		#add each record using the defined column fields in get_column_fields
		foreach ($records as $record) {
			$row = array($record,$record[$this->get_id_field()]);
			foreach ($this->get_column_fields() as $label => $field) {
				array_push($row,$record[$field]);
			}
			array_push($rows,$row);
		}	
		return $rows;
	}
	
	public function get_patient_records($args) {
		$id = trim(strtoupper(LRB_PatientLookup::get_value('id',$args)));
		$forename = trim(strtoupper(LRB_PatientLookup::get_value('fname',$args)));
		$surname = trim(strtoupper(LRB_PatientLookup::get_value('lname',$args)));
		$dob = trim(LRB_PatientLookup::get_value('dob',$args));
		$raw = LRB_PatientLookup::get_value('raw',$args);
		$hiss_record = array();

		#if ID exists get records by ID
		if ($id && strlen($id)>0) {
			$hiss_rec = $this->get_patient_record_by_id($id);
			if (!$hiss_rec) throw new Exception("Sorry the patient with identifier $id was not found");
			return $raw ? array($hiss_rec) : $this->convert_records_to_table(array($hiss_rec));
		}
		$err_text = "Sorry cannot carry out patient search. You must enter:";
		#if gets to here then either NO hospital no or NO hiss record obtained above for hospital number then search
		#by name/DOB
		$err = array();
        
        $noNameDob = false;
        !$surname and $noNameDob=true;
		!$forename and $noNameDob=true;
		!$dob and $noNameDob=true;
        
        if (!$id && $noNameDob) {
            throw new Exception("$err_text <ul><li>An S number or other hospital identifier</li></ul> OR <ul><li>first and last name AND date of birth</li></ul>");
        }
       
		#retrieve array of IDs for name/DOB
		$hiss_ids = $this->get_patient_ids_by_name_dob($surname, $forename, $dob, '');

		#retrieve all patients matching criteria
		$hiss_records = array();
		foreach ($hiss_ids as $id) {
			$hiss_record = $this->get_patient_record_by_id($id);
			if ($hiss_record) array_push($hiss_records, $hiss_record);
		}
		if (count($hiss_records)==0) {
			throw new Exception("No patients were found meeting your criteria");
		}
		return $raw ? $hiss_records : $this->convert_records_to_table($hiss_records);
	}
}
