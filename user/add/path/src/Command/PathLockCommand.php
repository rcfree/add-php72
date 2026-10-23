<?php

/**
 * Description of Load
 *
 * @author rcf8
 */

namespace ADD\Pathology\Command;
use CLE;
use ADD\Pathology\Data\RecordStatuses;

class PathLockCommand extends \ClinicLE\Command\AbstractCommand  {


	function getOptsList() {
		return "ui";
	}

	function getDescription() {
		return "Pathology lock management";
	}
	
 function runCommand(\ClinicLE\Common\Config\AbstractConfig $options) {

		$add = CLE::sys();
		$log = CLE::log($this);
        
		$dataSource = new \ADD_Pathology_DataSource_Warehouse_Lock($add->getConfig()->getSection("Pathology"));
		$lock = $dataSource->getLock();
		$log->info("Lock availability:".print_r($lock,1));
		if($options->has("u")) { 
    		if($lock['is_locked']==1) {
    			$dataSource->setAvailable();
    		}
    		else {
    			if($options->has("i")) {
					$log->info("[ignored] Cannot unlock pathology lock it is not active");
				}
				else {
					$log->error("Cannot unlock pathology lock it is not active");
				}
    		}
        }
        $log->complete();
	}
	
	function runAtEnd($exception=null) {
		
	}
}

?>
