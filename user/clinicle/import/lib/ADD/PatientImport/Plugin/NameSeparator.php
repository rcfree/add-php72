<?php
class ADD_PatientImport_Plugin_NameSeparator {
	function start($dataFile) {
		$headings = $dataFile->getHeadings();
		$headings = array_merge($headings,array("FIRST NAME","LAST NAME"));
		$dataFile->setHeadings($headings);
	}
	function afterRead(&$row) {
		$name = trim($row['NAME OF PATIENT']);
		if (strlen($name)==0) return;
		
		if (preg_match("/,/",$name)) {
			$names = explode(",",$name);
			if (count($names)==2) {
				$firstNames = explode(" ",trim($names[1]));
				$row['FIRST NAME']=strtoupper(trim($firstNames[0]));
				$row['LAST NAME']=strtoupper(trim($names[0]));
			}
		}
		else {
			$names = explode(" ",$name);
			if (count($names)>=2) {
				$row['FIRST NAME']=strtoupper(trim($names[0]));
				$row['LAST NAME']=strtoupper(trim($names[count($names)-1]));
			}
		}
	}
}
