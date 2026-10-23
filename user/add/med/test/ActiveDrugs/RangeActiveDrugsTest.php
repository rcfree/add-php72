<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugTimepointTest
 *
 * @author rcf8
 */
namespace ADD\Test\Med\ActiveDrugs;
use ADD\Med\ActiveDrugs\RangeActiveDrugs;
use ADD\Med\DrugConstants;

class RangeActiveDrugsTest extends \PHPUnit_Framework_TestCase {
	const JUNE_DMY="03/06/2015";
	const JUNE2_DMY="28/06/2015";
	const JULY_DMY="07/07/2015";
	const JULY2_DMY="18/07/2015";
	const MAY_DMY="12/05/2015";
	const MAY2_DMY="30/05/2015";
	const AUG_DMY="17/08/2015";
	
	const JUNE_YMD_HMS="2015-06-03 12:00:00";
	const JULY_YMD_HMS="2015-07-07 12:00:00";
	const MAY_YMD_HMS="2015-05-12 12:00:00";
	
	/**
	 * @dataProvider activeDrugNoSecondVisitProvider
	 * @test
	 */
	public function active_drug_no_second_visit_response($expected, $visitDate, $startDateType, $startDate, $dateCreated) {
		$timepoint = new RangeActiveDrugs($visitDate, null);
		$drug = array(
			"start_date_type"=>array("value"=>$startDateType),
			"current_status"=>array("value"=>DrugConstants::STATUS_ACTIVE),
			"start_date"=>array("value"=>$startDate),
			"end_date_known"=>array("value"=>null),
			"end_date"=>array("value"=>null),
			"date_created"=>$dateCreated
		);
	
		$this->assertEquals($expected, $timepoint->wasRecordActive($drug));
	}
	
	/**
	 * @dataProvider activeDrugWithSecondVisitProvider
	 * @test
	 */
	public function active_drug_with_second_visit_response($expected, $visit1Date, $visit2Date, $startDateType, $startDate, $dateCreated) {
		$timepoint = new RangeActiveDrugs($visit1Date, $visit2Date);
		$drug = array(
			"start_date_type"=>array("value"=>$startDateType),
			"current_status"=>array("value"=>DrugConstants::STATUS_ACTIVE),
			"start_date"=>array("value"=>$startDate),
			"end_date_known"=>array("value"=>null),
			"end_date"=>array("value"=>null),
			"date_created"=>$dateCreated
		);
	
		$this->assertEquals($expected, $timepoint->wasRecordActive($drug));
	}
	
	/**
	 * @dataProvider inactiveDrugWithSecondVisitProvider
	 * @test
	 */
	public function inactive_drug_with_second_visit_response($expected, $visit1Date, $visit2Date, $startDateType, $startDate, $endDateType, $endDate, $dateCreated, $dateUpdated) {
		$timepoint = new RangeActiveDrugs($visit1Date, $visit2Date);
		
		$drug = array(
			"start_date_type"=>array("value"=>$startDateType),
			"current_status"=>array("value"=>DrugConstants::STATUS_INACTIVE),
			"start_date"=>array("value"=>$startDate),
			"end_date_known"=>array("value"=>$endDateType),
			"end_date"=>array("value"=>$endDate),
			"date_created"=>$dateCreated,
			"date_updated"=>$dateUpdated
		);
	
		$this->assertEquals($expected, $timepoint->wasRecordActive($drug));
	}
	
	
	public function activeDrugNoSecondVisitProvider() {
		$provider = array(
			'true_if_visit1_date_is_the_start_date' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, null),
			'true_if_visit1_date_is_after_the_start_date' =>
				array(true,self::JULY_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, null),
			'true_if_visit1_date_is_before_the_start_date' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, null, self::JULY_YMD_HMS),
			'true_if_start_date_ongoing_and_visit1_date_is_the_date_created' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, self::JUNE_YMD_HMS),
			'true_if_start_date_is_gp_and_visit1_date_is_after_the_date_created' =>
				array(true,self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, self::JUNE_YMD_HMS),
			'true_if_start_date_is_prn_and_visit1_date_is_before_the_date_created' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_PRN, null, self::JULY_YMD_HMS),
		);
		return $provider;
	}
	
	public function activeDrugWithSecondVisitProvider() {
		$provider = array(
			'true_if_visit1_date_and_visit2_date_are_the_start_date' =>
				array(true,self::JUNE_DMY, self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, null),
			'true_if_visit1_date_is_after_the_start_date' =>
				array(true,self::JULY_DMY, null, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, null),
			'false_if_visit2_date_is_before_the_start_date' =>
				array(false,self::MAY_DMY, self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::JULY_DMY, null),
			'true_if_start_date_ongoing_and_visit1_date_is_the_date_created' =>
				array(true,self::JUNE_DMY, null, DrugConstants::START_DATE_ONGOING, null, self::JUNE_YMD_HMS),
			'true_if_start_date_is_gp_and_visit1_date_is_after_the_date_created' =>
				array(true,self::JULY_DMY, null, DrugConstants::START_DATE_ONGOING, null, self::JUNE_YMD_HMS),
			'true_if_start_date_is_prn_and_visit1_date_is_before_the_date_created' =>
				array(true,self::JUNE_DMY, null, DrugConstants::START_DATE_PRN, null, self::JULY_YMD_HMS),
		);
		return $provider;
	}
	/**
	 * @dataProvider inactiveDrugNoSecondVisitProvider
	 * @test
	 */
	public function inactive_drug_no_second_visit_response($expected, $visitDate, $startDateType, $startDate, $endDateType, $endDate, $dateCreated, $dateUpdated) {
		$timepoint = new RangeActiveDrugs($visitDate);
		
		$drug = array(
			"start_date_type"=>array("value"=>$startDateType),
			"current_status"=>array("value"=>DrugConstants::STATUS_INACTIVE),
			"start_date"=>array("value"=>$startDate),
			"end_date_known"=>array("value"=>$endDateType),
			"end_date"=>array("value"=>$endDate),
			"date_created"=>$dateCreated,
			"date_updated"=>$dateUpdated
		);
	
		$this->assertEquals($expected, $timepoint->wasRecordActive($drug));
	}
	
	public function inactiveDrugNoSecondVisitProvider() {
		$provider = array(
			'true_if_visit1_date_is_between_the_start_and_end_date' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'true_if_visit1_date_is_same_as_start_date' =>
				array(true,self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'true_if_visit1_date_is_same_as_end_date' =>
				array(true,self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::MAY_DMY,null,null),
			'true_if_visit1_date_is_before_the_start_date' =>
				array(true,self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'false_if_visit1_date_is_after_the_end_date' =>
				array(false,self::JULY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JUNE_DMY,null,null),
			'false_if_start_date_unknown_and_end_date_is_before_visit1_date'=>
				array(false, self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, null, self::JUNE_YMD_HMS),
			'true_if_start_date_unknown_and_date_created_is_same_as_visit1_date'=>
				array(true, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::JUNE_YMD_HMS, self::JUNE_YMD_HMS),
			'true_if_start_date_unknown_and_date_created_is_after_visit1_date'=>
				array(true, self::MAY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::JUNE_YMD_HMS, self::JULY_YMD_HMS),
			'false_if_end_date_unknown_and_date_updated_is_before_visit_date'=>
				array(false, self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_DMY, self::JUNE_DMY),
			'true_if_start_and_end_dates_unknown_and_visit_date_is_between_created_and_updated_date'=>
				array(true, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_YMD_HMS, self::JULY_YMD_HMS),
			'false_if_start_and_end_dates_unknown_and_created_date_is_null'=>
				array(false, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, null,null),
			'false_if_start_and_end_dates_unknown_and_updated_date_is_null'=>
				array(false, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_YMD_HMS,null),
			
		);
		return $provider;
	}
	
	public function inactiveDrugWithSecondVisitProvider() {
		$provider = array(
			'true_if_visits_are_between_the_start_and_end_date' =>
				array(true,self::JUNE_DMY, self::JUNE2_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'true_if_visit_dates_are_same_as_start_date' =>
				array(true,self::MAY_DMY, self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'true_if_visit2_date_is_same_as_end_date' =>
				array(true,self::MAY_DMY, self::JULY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'false_if_visit2_date_is_before_the_start_date' =>
				array(false,self::MAY_DMY, self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::JULY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'false_if_visits_are_after_the_end_date' =>
				array(false,self::JULY_DMY, self::JULY2_DMY,DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JUNE_DMY,null,null),
			'false_if_start_date_unknown_and_end_date_is_before_visits_range'=>
				array(false, self::JULY_DMY, self::JULY2_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, null, self::JUNE_YMD_HMS),
			'true_if_start_date_unknown_and_date_created_is_same_as_visit1_date'=>
				array(true, self::MAY_DMY, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_YMD_HMS, self::MAY_YMD_HMS),
			'true_if_start_date_unknown_and_date_created_is_same_as_visit2_date'=>
				array(true, self::MAY_DMY, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::JUNE_YMD_HMS, self::JUNE_YMD_HMS),
			'true_if_start_date_unknown_and_date_created_is_within_visits'=>
				array(true, self::MAY_DMY, self::JULY2_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::JUNE_YMD_HMS, self::JULY_YMD_HMS),
			'false_if_end_date_unknown_and_date_updated_is_before_visit1_date'=>
				array(false, self::JULY_DMY, self::JULY2_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_DMY, self::JUNE_DMY),
			'true_if_start_and_end_dates_unknown_and_visit_is_between_created_and_updated_date'=>
				array(true, self::JUNE_DMY, self::JUNE2_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_YMD_HMS, self::JULY_YMD_HMS),
			'false_if_start_and_end_dates_unknown_and_created_date_is_null'=>
				array(false, self::JUNE_DMY, self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, null,null),
			'false_if_start_and_end_dates_unknown_and_updated_date_is_null'=>
				array(false, self::JUNE_DMY, self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::MAY_YMD_HMS,null),
			
		);
		return $provider;
	}
	
	
	
}
