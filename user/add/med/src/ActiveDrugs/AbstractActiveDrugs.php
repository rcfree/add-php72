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


abstract class AbstractActiveDrugs {
	
	
	/**
	 * Extract all drugs from the input drug array that were active at the time point
	 * @param add $data
	 * @return type
	 */
	function extractActiveRecords($data) {
		$activeRecords = array();
		foreach($data as $item) {
			if($this->wasRecordActive($item)) {
				$activeRecords[]=$item;
			}
		}
		return $activeRecords;
	}
	
	/**
	 * Key logic which determines if a single drug record was active at the supplied time point
	 * @param type $record
	 * @return boolean
	 */
	abstract function wasRecordActive($record);
}
