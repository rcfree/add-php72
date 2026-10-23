<?php

/**
 * Determines if drugs were active at a specific timepoint, based on the the
 * current status (active or inactive) and the start/end date. 
 * If one or both of these are not known, then it will use the
 * date_created and date_updated respectively to determine this.
 *
 * @author Rob Free (rcf8@le.ac.uk)
 */
namespace ADD\Med\ActiveDrugs;
use ADD\Med\DrugConstants;

use DateTime;

class TimepointActiveDrugs extends AbstractActiveDrugs {
	
	private $timepointDate;
	
	/**
	 * A DD/MM/YYYY formatted date to use as timepoint
	 * @param type $timepointDate
	 */
	function __construct($timepointDate) {
		if(!$timepointDate) {
			throw new \InvalidArgumentException("You cannot provide a null timepoint date to the constructor");
		}
		$this->timepointDate = $timepointDate;
	}
	
	/**
	 * Key logic which determines if a single drug record was active at the supplied time point
	 * @param type $record
	 * @return boolean
	 */
	function wasRecordActive($record) {
		$startDateType = $record['start_date_type']['value'];
		$currentStatus = $record['current_status']['value'];
		$timepointDt = DateTime::createFromFormat("d/m/Y",$this->timepointDate);
		
		//default date time will have current time
		$timepointDt->setTime(12,0,0);

		$startDt = null;
		$endDt = null;
		
		if($startDateType == DrugConstants::START_DATE_KNOWN) {
			$startDt = DateTime::createFromFormat("d/m/Y",$record['start_date']['value']);
		}
		else {
			$startDt = DateTime::createFromFormat("Y-m-d H:i:s", $record['date_created']);
			
		}
		if($startDt) {
			$startDt->setTime(12,0,0);
		}
		if($currentStatus==DrugConstants::STATUS_ACTIVE) {
			if($startDt<=$timepointDt) {
				return true;
			}
			return false;
		}
		
		$endDateKnown = $record['end_date_known']['value'];
		
		if($endDateKnown == DrugConstants::END_DATE_KNOWN) {
			$endDt = DateTime::createFromFormat("d/m/Y",$record['end_date']['value']);
		}
		else {
			$endDt = DateTime::createFromFormat("Y-m-d H:i:s", $record['date_updated']);
			
		}
		if($endDt) {
			$endDt->setTime(12,0,0);
		}
		if($startDt<=$timepointDt && $endDt>=$timepointDt) {
			return true;
		}
		
		return false;
	}
}
