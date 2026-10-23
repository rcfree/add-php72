<?php

class ADD_Page_Api_Research_StudyBreakdown extends ADD_Page_Api {
    public function createMenuPath() {
        return "api/research/study-breakdown";
    }

	public function apiOutput($values) {
		$studyQuery = new ADD_ResearchSearch_StudyQuery(1,null);
        $studyQuery->setIncludeStudies(true);
        list($sql,$params) = $studyQuery->generateSQL();
        $db = CLE::svc(\ClinicLE\System\Service\CoreServices::MAIN_CONNECTION);
        $db->execute("drop table if exists `study_breakdown`");
        $db->execute("create table `study_breakdown` as ".$sql,$params);

        $flatData = $db->fetchAll("select study, study_status, count(patient_id) as patient_count from 
        `study_breakdown` where study is not null group by study, study_status");
        $matrixConv = new ADD_Client_Util_MatrixConvert("study_status","study","patient_count");

        $matrix = $matrixConv->convertDataToXKeyed($flatData,true);
	    return array("data"=>$matrix);
		
	}
}