<?php

class ADD_Page_Api_Research_AbstractSearch extends ADD_Page_Api {
	
	function addFiltersToQuery($query) {
	}
	
	public function apiOutput($values) {
		$searchPage = ADD::sys()->getPageFromPim("research:search");	
		if(!$searchPage->getPermission('status_search')) {
			throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to search research participants");
		}
		$pageNo = ADD::sys()->getParam('page_no',1);
		$rowCount = 50;
		$query = new ADD_ResearchSearch_StudyQuery($pageNo,$rowCount);
		$this->addFiltersToQuery($query);
		
		$rows = $query->getRows();
		
		$detailPage = ADD::sys()->getPageFromPim("research:details");
		$detailForm = $detailPage->getComponent('Form');
		$detailForm->setup();
		$currentStatus = $detailForm->getField("current_status");
		$studyDetailPage = ADD::sys()->getPageFromPim("research:sdetails");
		$studyDetailForm = $studyDetailPage->getComponent('Form');
		$studyDetailForm->setup();
		$study = $studyDetailForm->getField("study");
		$studyStatus = $studyDetailForm->getField("current_status");
		$count=0;
		foreach($rows as $row) {
			$currentStatus->setValue($row['current_status']);
			$rows[$count]['current_status']=$currentStatus->getDisplayText($detailPage,null);
			if($query->doesIncludeStudies()) {
				$studyStatus->setValue($row['study_status']);
				$study->setValue($row['study']);
				$studyStatusText = $studyStatus->getDisplayText($studyDetailPage,null);
				$rows[$count]['study_status']=$studyStatusText;
				$rows[$count]['study']=$study->getDisplayText($studyDetailPage,null);
			}
			ADD_Util::convertDateFromDb($rows[$count],'date_of_birth');
			$count++;
		}
		$first = $rowCount * ($pageNo-1)+1;
        $last = $first + count($rows)-1;
		return array("data"=>$rows,"page_no"=>$pageNo,"rows_per_page"=>$rowCount,"total"=>$query->countRows(), "first"=>$first,"last"=>$last);
		
	}
}