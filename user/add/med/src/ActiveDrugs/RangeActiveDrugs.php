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

class RangeActiveDrugs extends AbstractActiveDrugs {
	
	private $visit1Date;
	private $visit2Date;
	
	/**
	 * A DD/MM/YYYY formatted date to use as timepoint
	 * @param type $visit1
	 */
	function __construct($visit1Date, $visit2Date=null) {
		if(!$visit1Date) {
			throw new \InvalidArgumentException("You cannot provide a null visit 1 date to the constructor");
		}
		$this->visit1Date = $visit1Date;
		$this->visit2Date = $visit2Date;
	}
	
	/**
	 * Key logic which determines if a single drug record was active at the supplied time point
	 * @param type $record
	 * @return boolean
	 */
	function wasRecordActive($record) {
		if($this->visit2Date) {
			return $this->dealWithTwoVisits($record);
		}
		
		
		return $this->dealWithOneVisit($record);
		
	}

	private function dealWithOneVisit($record) {
		$currentStatus = $record['current_status']['value'];
		if($currentStatus==DrugConstants::STATUS_ACTIVE) {
			return true;
		}
		
		$visit1Dt = DateTime::createFromFormat("d/m/Y",$this->visit1Date);
		
		//default date time object seems to have current time
		$visit1Dt->setTime(0,0,0);

		$endDt = $this->getEndDateTime($record);
		
		if($endDt<$visit1Dt) {
			return false;
		}
		
		return true;
	}
	private function getStartDateTime($record) {
		$startDateType = $record['start_date_type']['value'];
		
		if($startDateType == DrugConstants::START_DATE_KNOWN) {
			$startDt = DateTime::createFromFormat("d/m/Y",$record['start_date']['value']);
		}
		else {
			$startDt = DateTime::createFromFormat("Y-m-d H:i:s", $record['date_created']);
			
		}
		if($startDt) {
			//standardise time as 00:00 whether source was time or date
			$startDt->setTime(0,0,0);
		}
		return $startDt;
	}
	private function getEndDateTime($record) {
		$endDateKnown = $record['end_date_known']['value'];
		
		if($endDateKnown == DrugConstants::END_DATE_KNOWN) {
			$endDt = DateTime::createFromFormat("d/m/Y",$record['end_date']['value']);
		}
		else {
			$endDt = DateTime::createFromFormat("Y-m-d H:i:s", $record['date_updated']);
		}

		if($endDt) {
			//standardise time as 00:00 whether source was time or date
			$endDt->setTime(0,0,0);
		}
		return $endDt;
	}
	private function dealWithTwoVisits($record) {
		//default date time object created with current time so standardise on 00:00:00
		$visit1Dt = DateTime::createFromFormat("d/m/Y",$this->visit1Date);
		$visit1Dt->setTime(0,0,0);
		
		$visit2Dt = DateTime::createFromFormat("d/m/Y",$this->visit2Date);
		$visit2Dt->setTime(0,0,0);
		
		$startDt = $this->getStartDateTime($record);
		
		$currentStatus = $record['current_status']['value'];
		if($currentStatus==DrugConstants::STATUS_ACTIVE) {
			if($visit2Dt<$startDt) {
				return false;
			}
			return true;
		}
		$endDt = $this->getEndDateTime($record);
		if($visit2Dt<$startDt) {
			return false;
		}
		if($visit1Dt>$endDt) {
			return false;
		}
		return true;
	}
}
