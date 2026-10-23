<?php

class ADD_Page_Api_Research_DbCounts extends ADD_Page_Api {
    public function createMenuPath() {
        return "api/research/db-counts";
    }

	public function apiOutput($values) {
        $cdb = ADD::db('consent');
        $cdb->createConsentRecord(true);

        $html = new ADD_Content_Html();
        $cdb->execute("drop temporary table if exists `current_consent_form`");
        $cdb->execute("create temporary table `current_consent_form` as select 
left(group_concat(consent_form_date order by consent_form_date desc),10) as `latest_consent_form_date`,
group_concat(consent_form_date order by consent_form_date desc) as `consent_form_dates`,
left(group_concat(add_to_recruit_db order by consent_form_date desc),1) as `latest_add_to_recruit_db`,
group_concat(add_to_recruit_db order by consent_form_date desc) as `add_to_recruit_dbs`,
left(group_concat(add_to_research_db order by consent_form_date desc),1) as `latest_add_to_research_db`,
group_concat(add_to_research_db order by consent_form_date desc) as `add_to_research_dbs`,
current_status, ilh_identifier
from consent_record
group by patient_id");

        $wheres = array(
            "Recruit OR research"=>" `latest_add_to_recruit_db`='Y' or `latest_add_to_research_db`='Y'",
            "Recruit AND research"=>"`latest_add_to_recruit_db`='Y' and `latest_add_to_research_db`='Y'",
            "All recruits"=>"`latest_add_to_recruit_db`='Y'",
            "All research"=>"`latest_add_to_research_db`='Y'",
            "Recruit only"=>"`latest_add_to_recruit_db`='Y' and `latest_add_to_research_db`!='Y'",
            "Research only"=>"`latest_add_to_recruit_db`!='Y' and `latest_add_to_research_db`='Y'",
            "No to research AND recruit"=>"`latest_add_to_recruit_db`!='Y' and `latest_add_to_research_db`!='Y'",
            "No consent form"=>"(`add_to_recruit_dbs`='' and `add_to_research_dbs`='') or (add_to_recruit_dbs is null 
            and 
            add_to_research_dbs is null)"
        );
        $data = array();
        foreach($wheres as $type => $where) {
            $data[] = array("count_type" => $type, "count" => $cdb->fetchSingleValue("select count(*) from 
            `current_consent_form` where " . $where));
        }
        return array("data"=>$data);
	}
}