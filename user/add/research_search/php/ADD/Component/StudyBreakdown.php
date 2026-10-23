<?php

class ADD_Component_StudyBreakdown extends ADD_Component {
	public function menuPath(&$menuPath) {
		$menuPath="study/breakdown";
	}
	public function title(&$title) {
		$title="Study breakdown";
	}
	public function isPaged() {
		return true;
	}
	public function generate() {
	    /*
        $cdb = ADD::db('consent');
        $cdb->createConsentRecord();

        $html = new ADD_Content_Html();
        $cdb->execute("create temporary table `current_consent_form` as select 
left(group_concat(consent_form_date order by consent_form_date desc),10) as `latest_consent_form_date`,
left(group_concat(add_to_recruit_db order by consent_form_date desc),1) as `latest_add_to_recruit_db`,
left(group_concat(add_to_research_db order by consent_form_date desc),1) as `latest_add_to_research_db`,
current_status, ilh_identifier
from consent_record
group by patient_id");

        $recruitOrResearch = $cdb->fetchSingleValue("select count(*) from `current_consent_form` where 
        `latest_add_to_recruit_db`='Y' or `latest_add_to_research_db`='Y'");
        $recruitAndResearch = $cdb->fetchSingleValue("select count(*) from `current_consent_form` where 
        `latest_add_to_recruit_db`='Y' and `latest_add_to_research_db`='Y'");
        $recruitOnly = $cdb->fetchSingleValue("select count(*) from `current_consent_form` where 
        `latest_add_to_recruit_db`='Y' and `latest_add_to_research_db`!='Y'");
        $allRecruit = $cdb->fetchSingleValue("select count(*) from `current_consent_form` where 
        `latest_add_to_recruit_db`='Y'");
        $allResearch = $cdb->fetchSingleValue("select count(*) from `current_consent_form` where
        `latest_add_to_research_db`='Y'");
        $researchOnly = $cdb->fetchSingleValue("select count(*) from `current_consent_form` where 
        `latest_add_to_recruit_db`!='Y' and `latest_add_to_research_db`='Y'");

        $html->startElement("div",array("class"=>"alert alert-info"));
        $html->addTextElement("p","Research database counts",array("class"=>"lead"));
        $html->addTextElement("p","<b>Either recruit and research:</b> ".$recruitOrResearch);
        $html->addTextElement("p","<b>Both recruit and research:</b> ".$recruitAndResearch);
        $html->addTextElement("p","<b>All research:</b> ".$allResearch);
        $html->addTextElement("p","<b>All recruit:</b> ".$allRecruit);
        $html->addTextElement("p","<b>Recruit only:</b> ".$recruitOnly);
        $html->addTextElement("p","<b>Research only:</b> ".$researchOnly);

        $html->endElement("div");
*/
	    $content = ADD::web()->getContent();
        $dccontainer = new \ClinicLE\Client\Container\Container("research-dbcounts","EncounterGridController");

        $dccontainer->appendAttrs(array("cle-parentid"=>"",
            "cle-pid"=>"",
            "cle-refresh"=>"research-breakdowns",
            "cle-title"=>"",
            "cle-dataurl"=>"/api/research/db-counts"));


        $dctable = new \ClinicLE\Client\Container\Table("research database count","control.data.grid","row");
        $dctable->addCell(new \ClinicLE\Client\Container\Cell\FieldCell("Count description","count_type"));
        $dctable->addCell(new \ClinicLE\Client\Container\Cell\FieldCell("Count","count"));
        $dccontainer->addToContent($dctable);

        $content->addHtml($dccontainer->createHtml());

        $rccontainer = new \ClinicLE\Client\Container\Container("research-search","EncounterGridController");

        $rccontainer->appendAttrs(array("cle-parentid"=>"",
            "cle-pid"=>"",
            "cle-refresh"=>"research-breakdowns",
            "cle-title"=>"",
            "cle-dataurl"=>"/api/research/study-breakdown"));


		$rctable = new \ClinicLE\Client\Container\Table("study breakdown","control.data.grid","row");
        $rccontainer->putProperty("custom_pattern","cell.value");
        $rctable->addCell(new \ClinicLE\Client\Container\Cell\MatrixCell("10em","10em"));
        $rccontainer->addToContent($rctable);

        $content->addHtml($rccontainer->createHtml());
	}

	public function expectedPermissions(&$expPerms) {
		$expPerms[]="view";
	}
}

