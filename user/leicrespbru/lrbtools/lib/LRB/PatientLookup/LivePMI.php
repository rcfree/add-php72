<?php
class LRB_PatientLookup_LivePMI extends LRB_PatientLookup {
	
	function __construct($config) {
	    $db = new PDO($config['dsn'],$config['user'],$config['pass']);
		
		$this->db = $db;
	}

	protected function get_id_field() {
		return 'main_pat_id';
	}
	
	protected function get_id_label() {
		return 'S number';
	}

	protected function get_column_fields() {
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
	private function mssqldate_to_dmy($msdate) {
	    if (empty($msdate)) return "";
	    $date = date_parse($msdate);
        return sprintf("%02d",$date['day'])."/".sprintf("%02d",$date['month'])."/".sprintf("%04d",$date['year']);              
	}
	protected function get_patient_record_by_id($id) {
		$db = $this->db;
		$sql = 'SELECT * FROM LRBRU.UHL_PMI_QUERY_BY_ID(?)';
        $smt = $db->prepare($sql);
        $smt->execute(array($id));
        $rows = $smt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows)==1) {
        	$pmi_rec = $rows[0];
        	if (!isset($pmi_rec['main_pat_id'])) {
        		return null;
        	}
        	$dob = $pmi_rec['dob'];
        	$date = date_parse($dob);
			$pmi_rec['dob']=$this->mssqldate_to_dmy($pmi_rec['dob']);
			$pmi_rec['date_of_death']=$this->mssqldate_to_dmy($pmi_rec['date_of_death']);   
            
            return $pmi_rec;
        }
        else {
        	return null;
        }
	}

	protected function get_patient_ids_by_name_dob($surname, $forename, $dob) {
		$db = $this->db;
		$fdob = DateTime::createFromformat("j/n/Y",$dob);
		$dob = $fdob->format("Y-m-d");
		$sql = 'SELECT main_pat_id FROM LRBRU.UHL_PMI_QUERY_BY_NAME(?,?,?,NULL)';
        $smt = $db->prepare($sql);
        $smt->bindValue(1,$surname,PDO::PARAM_STR);
		$smt->bindValue(2,$forename,PDO::PARAM_STR);
		$smt->bindValue(3,$dob,PDO::PARAM_STR);
		$smt->execute();

        $rows = $smt->fetchAll();
        
        $pmi_records = array();
        foreach($rows as $row) {
        	$pmi_records[]=$row['main_pat_id'];
        }
		return $pmi_records;
	}
}
