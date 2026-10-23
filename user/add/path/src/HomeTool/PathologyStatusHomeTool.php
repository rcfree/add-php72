<?php

namespace ADD\Pathology\HomeTool;

use ADD\Pathology\Resource\PathologyServices;
use ADD\Pathology\Resource\TransferEngager;
use ClinicLE\Options\HomeTool\AbstractHomeTool;
use CLE;

class PathologyStatusHomeTool extends AbstractHomeTool {
	private $toolClass = "";
	private $config;
    
	public function createTool() {
		$patientId = CLE::sys()->getPatientId();
		if(!$patientId) return;
		
		$html = CLE::web()->newContent();	
		$transferStatus = CLE::svc(PathologyServices::TRANSFER_STATUS);
        $engager = CLE::svc(PathologyServices::TRANSFER_ENGAGER);

		if($transferStatus->getNotification()) {
			$html->addTextElement("p",$transferStatus->getNotification());
			$this->toolClass = "tool-warn";
			return $html;
		}

        $engager->init($patientId, $transferStatus->assertLogConfigFile());

		$engager->doTransfer(CLE::sys()->getParam('forcepath'));
       	sleep(1);
		$noMessage = $this->transferHadError($html, $engager);
		return $noMessage ? null : $html;
	}
	
	private function transferHadError($html, TransferEngager $engager) {
        $transferStatus = CLE::svc(PathologyServices::TRANSFER_STATUS);

        $patientId = $engager->getPatientId();
		list($status, $statusMessage, $statusTs, $completeTs) = $transferStatus->retrieveDetails($patientId);
		$this->toolClass = "tool-info";
		
		if($engager->isTriggered()) {
			$html->addTextElement("p",$this->getTransferText($engager->getTriggerTime()));
			return;
		}
		
		$noMessage = false;
				
        list($statusText, $reattempt, $warning) = $transferStatus->determineStatusOutput($status,$statusTs,
            $completeTs);
		$html->addTextElement("p",$statusText);
        
		if($warning) {
		    $this->toolClass = "tool-warn";
        }

		if ($reattempt) {
		    $html->addTextElement("a","Reattempt 
			transfer", array("href"=>"javascript:;","ng-click"=>"gotoPage('/path/reattempt?pid=".$patientId."')","class"=>"btn"));
		}

		return $noMessage;
	}

	public function getTransferText($triggerTime) {
		return "Pathology data transfer started at ".$triggerTime." for this patient.<br/>You may need to wait a few minutes before attempting to view it.";
	}
	public function toolTitle() {
		return "Pathology Import";
	}
	public function toolWeight() {
		return 1;
	}
	public function isAvailable() {
		if(!CLE::sys()->getAccessManager()->isLoggedIn()) return false;
		$modPages = CLE::sys()->getModule("path")->getPages();
		$anyPerms = false;
		foreach ($modPages as $modPage) {
			if(!$modPage->hasAnyPermissions(array('create','edit','view'))) {
				continue;
			}
			$anyPerms=true;
			break;
		}
		if(!$anyPerms) {
			return false;
		}
		return true;
	}
	public function toolClass() {
		return $this->toolClass;
	}
	

}
