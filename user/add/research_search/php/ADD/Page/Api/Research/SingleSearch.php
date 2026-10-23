<?php

class ADD_Page_Api_Research_SingleSearch extends ADD_Page_Api_Research_AbstractSearch {
	public function createMenuPath() {
		return "api/research/single-search";
	}
	public function addFiltersToQuery($query) {
		$searchType = ADD::sys()->assertParam('search_type');
		$query->setIncludeStudies(true);
		
		if($searchType==1) {
			$researchId = ADD::sys()->getParam('research_id');
			if(!$researchId) {
				throw new Exception("You must enter a research ID");
			}
			$query->filterByResearchId($researchId);
		}
		else {
			$study = ADD::sys()->getParam('study');
			$studyNo = ADD::sys()->getParam('study_no');
			if(!$study || !$studyNo) {
				throw new Exception("You must choose a study and a study number");
			}
			
			$query->filterByStudy($study);
			$query->filterByStudyNo($studyNo);
			
		}
	}
	
}