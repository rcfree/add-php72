<?php

class LRB_PatientLookup_TestPMI extends LRB_PatientLookup {
	public $data = array(
	array("Z0000001","CAPES","A","JEFF","04/03/2001","SINGLE","M","WHITE","2342345","DR","AHMED","AKHBAR","GP_ADDRESS_1","GP_ADDRESS2","GP_ADDRESS3","GP_ADDRESS4","GP_POSTCODE","GP_WORKPHONE","PAT_ADD1","PAT_ADD2","PAT_ADD3","POSTCODE","HOMEPHONE","F12342"),
	array("Z0000002","HOLMES","","SHERLOCK","03/04/1936","SINGLE","M","WHITE","2342346","DR","JOHN","WATSON","221B BAKER STREET","MARYLEBONE","LONDON","","W1","GP_WORKPHONE","221B BAKER STREET","MARYLEBONE","LONDON","W1","56","F221221"),		
	array("Z0000003","WATSON","","JOHN","03/04/1930","SINGLE","M","WHITE","9876553","MR","SHERLOCK","HOLMES","221B BAKER STREET","MARYLEBONE","LONDON","","W1","GP_WORKPHONE","221B BAKER STREET","MARYLEBONE","LONDON","W1","56","R221221"),			
	array("Z0000004","CAPES","A","JEFF","04/03/2001","SINGLE","M","WHITE","2342347","DR","AHMED","AKHBAR","GP_ADDRESS_1","GP_ADDRESS2","GP_ADDRESS3","GP_ADDRESS4","GP_POSTCODE","GP_WORKPHONE","PAT_ADD1","PAT_ADD2","PAT_ADD3","POSTCODE","HOMEPHONE","F12346"),
	array("Z0000005","CAPES","G","JENNIFER","03/04/1997","SINGLE","F","WHITE","2342348","DR","AHMED","AKHBAR","GP_ADDRESS_1","GP_ADDRESS2","GP_ADDRESS3","GP_ADDRESS4","GP_POSTCODE","GP_WORKPHONE","PAT_ADD1","PAT_ADD2","PAT_ADD3","POSTCODE","HOMEPHONE","F12345"),
	);
	private $by_id=array();
	private $by_name_dob=array();

	function __construct() {
		$field_order=array("main_pat_id","last_name","middle_name","first_name","dob","marital_status","gender","ethnicity","nhs_number","gp_title","gp_forename","gp_surname",
	"gp_addr1","gp_addr2","gp_addr3","gp_addr4","gp_postcode","gp_workphone","pat_addr1","pat_addr2","pat_addr3","postcode","homephone","hospital_no");

		foreach($this->data as $item) {
			$record = array();
			$counter = 0;
			foreach($field_order as $field) {
				$record[$field]=$item[$counter];
				$counter++;
			}
			#$record{'dob'}.=" 00:00:00.000";
			$this->by_id[$record['hospital_no']]=$record;
			$name_dob = $record['first_name'].":".$record['last_name'].":".$record['dob'];
			!isset($this->by_name_dob[$name_dob]) and $this->by_name_dob[$name_dob]=array();
			array_push($this->by_name_dob[$name_dob],$record['main_pat_id']);
			$this->by_id[$record['main_pat_id']]=$record;
		}
	}

	public function get_id_field() {
		return 'main_pat_id';
	}
	
	public function get_id_label() {
		return 'S number';
	}
	
	public function get_column_fields() {
		return array(
			'Gender'=> 'gender',
			'Last name'=> 'last_name',
			'First name'=>'first_name',
			'Middle name'=>'middle_name',
			'Date of Birth'=>'dob',
			'Address line 1' => 'pat_addr1',
			'Postcode' => 'postcode'
		);
	}
	
	protected function get_patient_record_by_id($id) {
		$hiss_record = isset($this->by_id[$id]) ? $this->by_id[$id] : null;
		if (!$hiss_record) throw new Exception("Sorry the patient with identifier $id was not found");

		#$hiss_record['Dob_dmy']=$this->mssql_to_dmy($hiss_record{Dob});
		#$hiss_record['Dob']=$hiss_record{Dob}=~/(.+)\ /;
		#$hiss_record['Dob'] = $hiss_record['Dob_dmy'];
		return $hiss_record;

	}

	protected function get_patient_ids_by_name_dob($surname, $forename, $dob) {
		#$new_dob=substr($dob,6,4)."-".substr($dob,3,2)."-".substr($dob,0,2)." 00:00:00.000";
		$combined = $forename.":".$surname.":".$dob;
		if(isset($this->by_name_dob[$combined])) {
			return $this->by_name_dob[$combined];
		}
		else {
			throw new Exception("Patient record for $surname, $forename ($dob) is not available");
		}
	}
}
