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
use ADD\Med\ActiveDrugs\TimepointActiveDrugs;
use ADD\Med\DrugConstants;

class TimepointActiveDrugsTest extends \PHPUnit_Framework_TestCase {
	const JUNE_DMY="03/06/2015";
	const JULY_DMY="07/07/2015";
	const MAY_DMY="12/05/2015";
	
	const JUNE_YMD_HMS="2015-06-03 12:00:00";
	const JULY_YMD_HMS="2015-07-07 12:00:00";
	const MAY_YMD_HMS="2015-05-12 12:00:00";
	
	/**
	 * @dataProvider activeDrugDateCombinationProvider
	 * @test
	 */
	public function active_drug_date_response($expected, $visitDate, $startDateType, $startDate, $dateCreated) {
		$timepoint = new TimepointActiveDrugs($visitDate);
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
	
	public function activeDrugDateCombinationProvider() {
		$provider = array(
			'true_if_visit_date_is_the_start_date' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, null),
			'true_if_visit_date_is_after_the_start_date' =>
				array(true,self::JULY_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, null),
			'false_if_visit_date_is_before_the_start_date' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, null, self::JULY_YMD_HMS),
			'true_if_start_date_ongoing_and_visit_date_is_the_date_created' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, self::JUNE_YMD_HMS),
			'true_if_start_date_is_gp_and_visit_date_is_after_the_date_created' =>
				array(true,self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, self::JUNE_YMD_HMS),
			'false_if_start_date_is_prn_and_visit_date_is_before_the_date_created' =>
				array(false,self::JUNE_DMY, DrugConstants::START_DATE_PRN, null, self::JULY_YMD_HMS),
		);
		return $provider;
	}
	
	/**
	 * @dataProvider inactiveDrugDateCombinationProvider
	 * @test
	 */
	public function inactive_drug_date_response($expected, $visitDate, $startDateType, $startDate, $endDateType, $endDate, $dateCreated, $dateUpdated) {
		$timepoint = new TimepointActiveDrugs($visitDate);
		
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
	
	public function inactiveDrugDateCombinationProvider() {
		$provider = array(
			'true_if_visit_date_is_between_the_start_and_end_date' =>
				array(true,self::JUNE_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'true_if_visit_date_is_same_as_start_date' =>
				array(true,self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'true_if_visit_date_is_same_as_end_date' =>
				array(true,self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'false_if_visit_date_is_before_the_start_date' =>
				array(false,self::MAY_DMY, DrugConstants::START_DATE_KNOWN, self::JUNE_DMY, DrugConstants::END_DATE_KNOWN, self::JULY_DMY,null,null),
			'false_if_visit_date_is_after_the_end_date' =>
				array(false,self::JULY_DMY, DrugConstants::START_DATE_KNOWN, self::MAY_DMY, DrugConstants::END_DATE_KNOWN, self::JUNE_DMY,null,null),
			'false_if_start_date_unknown_and_date_is_before_visit_date'=>
				array(false, self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, null, self::JUNE_YMD_HMS),
			'true_if_start_date_unknown_and_date_created_is_same_as_visit_date'=>
				array(true, self::JUNE_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::JUNE_YMD_HMS, self::JULY_YMD_HMS),
			'false_if_start_date_unknown_and_date_created_is_after_visit_date'=>
				array(false, self::JULY_DMY, DrugConstants::START_DATE_ONGOING, null, DrugConstants::END_DATE_UNKNOWN, null, self::JUNE_DMY, null),
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
	
	
	
}
