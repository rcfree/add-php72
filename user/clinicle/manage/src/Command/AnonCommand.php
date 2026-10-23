<?php

/**
 * Description of Load
 *
 * @author rcf8
 */

namespace ClinicLE\Manage\Command;

class AnonCommand implements ADD_ManageInterface {

	function getOptsList() {
		return "u:t";
	}

	function getDescription() {
		return "Anonymise database";
	}
	private $gens=array();
	
	function manage($options) {
		$add = ADD::sys();
		$log = ADD::log($this);
		$db = ADD::db();
		$config = $add->getConfig()->getSection('Database');

		echo "This WILL anonymise the existing ADD database (" . $config->getSetting('dsn') . "). This includes removing all audit information.\nAre you sure (Y/n)?\n";
		$value = ADD_Util::inputLine(1);
		if ($value != "Y")
			throw new Exception("User cancelled operation");

		$log = ADD::log();
		$patients = $db->fetchAll("select * from patient");
		$fnames = array(
			"M" => array("jeff", "fred", "george", "peter", "sid", "aaron", "robert", "saj", "matt", "archibald", "jim", "james", "john", "oliver", "george"),
			"F" => array("margaret", "judy", "katherine", "lorna", "christina", "linda", "sarah", "wendy", "gladys", "jane", "olivia", "katherine", "georgina")
		);
		$lnames = array("fredericks", "james", "smith", "o'donnell", "mcdonald", "clooney", "arkwright", "royce", "freeman", "davids", "roberts", "hartman");
		$egroups = array("A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "R", "S", "Z");

		foreach ($patients as $patient) {
			$fname = strtoupper(self::randomFromList($fnames[$patient['gender']]));
			$lname = strtoupper(self::randomFromList($lnames));
			$ethnicity = self::randomFromList($egroups);
			$dob = $this->modifyDob($patient['date_of_birth']);
			$id = $this->generateSNo();
			$log->info("new patient:$id = $fname $lname, gender:{$patient['gender']}, ethnic group:" . $ethnicity);
			$log->info("new dob " . $patient['date_of_birth'] . "=>" . $dob);
			$patient['identifier'] = $id;
			$patient['first_name'] = $fname;
			$patient['last_name'] = $lname;
			$patient['middle_name'] = null;
			$patient['ethnic_group'] = $ethnicity;
			$patient['nhs_no'] = null;
			$patient['hospital_no'] = null;

			
			$patient['address_id'] = null;
			$patient['gp_id'] = null;
			$db->update('patient', $patient);
			$db->execute("delete from address where patient_id=?", array($patient['id']));

		}

		$log->info("Delete observation audits");
		$db->execute("delete from observation_audit");

		$log->info("Delete observation field audits");
		$db->execute("delete from observation_field_audit");

		$log->info("Delete audits");
		$db->execute("delete from audit");

		$log->info("Delete 'Memo' observations");
		$memos = $db->fetchAll("select * from observation_field where type='memo'");
		foreach ($memos as $memo) {
			$mobs = $db->fetchAll("select * from observation where observation_field_id=?", array($memo['id']));
			foreach ($mobs as $mob) {
				$db->execute("update observation_note set text='' where observation_id=?", array($mob['id']));
			}
		}
		$db->execute("delete from address");
		$db->Execute("delete from gp");
	}

	static function randomFromList($array) {
		return $array[rand(0, count($array) - 1)];
	}

	static function modifyDob($dob) {
		$dt = DateTime::createFromFormat("Y-m-d", $dob);
		if (!$dt)
			return null;

		if (rand(0, 1)) {
			$dt->add(new DateInterval("P" . rand(0, 100) . "D"));
		} else {
			$dt->sub(new DateInterval("P" . rand(0, 100) . "D"));
		}
		return $dt->format("Y-m-d");
	}

	function generateSNo() {
		global $gens;
		GEN:
		$sno = rand(1000000, 9999999);
		if (isset($this->gens[$sno]))
			return $this->generateSNo();
		$this->gens[$sno] = 1;
		return "Z" . $sno;
	}

}

?>