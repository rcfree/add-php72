<?php

class ADD_Page_Api_Research_MultiSearch extends ADD_Page_Api_Research_AbstractSearch {
	public function createMenuPath() {
		return "api/research/multi-search";
	}
	
	public function addFiltersToQuery($query) {
		$query->setIncludeStudies(true);
		$query->filterByStatus(ADD::sys()->getParam('status'));
		$query->filterByStudy(ADD::sys()->getParam('study'));
		$query->filterByStudyStatus(ADD::sys()->getParam('study_status'));
	}
	
}