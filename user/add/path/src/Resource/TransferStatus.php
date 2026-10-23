<?php
namespace ADD\Pathology\Resource;
use ADD_PathologyFactory_Haem;
use ADD_Data_PathologyService;
use ADD\Pathology\Data\RecordStatuses;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\System\Event\EventDispatcher;
use DateInterval;
use DateTime;

class TransferStatus {
    private $config;
    function __construct($config, EventDispatcher $events) {
        $this->config = $config;
        $this->events = $events;
    }
    function getNotification() {
        return $this->config->getSetting('notification');
    }
    function assertLogConfigFile() {
        return $this->config->assertSetting("log_config_file");
    }
    function retrieveDetails($patientId) {    
        $factory = new ADD_PathologyFactory_Haem($this->config,$this->events);
        $pathologyService = new ADD_Data_PathologyService($factory,$this->events);
        
        $patient = CLE::db('patient')->getPatient($patientId);
        $patientStatus = $pathologyService->retrievePatientStatus($patient['identifier']);
        
        $status = $patientStatus->getObsValue("transfer_status");
        $statusMessage = $patientStatus->getObsValue("status_message");
        $statusTs = $patientStatus->getObsValue("status_timestamp");
        $completeTs = $patientStatus->getObsValue("complete_timestamp"); 
        return array($status, $statusMessage, $statusTs, $completeTs);
    }
    
    function determineStatusOutput($status, $statusTs, $completeTs, $messagePrefix = "Transfer" ) {
        $transferFailed = $this->hasTransferFailed($statusTs);
        $statusText = "";
        $warning = false;
        $reattempt = false;
        
        switch($status) {
            case RecordStatuses::COMPLETE:
                $statusText = $messagePrefix." completed at $completeTs";
                break;
            case RecordStatuses::PENDING:
                if(!$transferFailed) {
                    $statusText = $messagePrefix." queued at $statusTs";
                    break;
                }
                
                $statusText = $messagePrefix." queued at $statusTs still has not run";
                $reattempt = false;
                $warning = true;
                break;
            
                
            case RecordStatuses::IN_PROGRESS:
                if(!$transferFailed) {
                    $statusText = $messagePrefix." in progress at $statusTs";
                    break;
                }
                $statusText = $messagePrefix." in progress at $statusTs has still not completed";
                $warning = true;
                break;
                
            case RecordStatuses::ERROR:
                $statusText = $messagePrefix." error at ".$statusTs;          
                $reattempt = true;
                $warning = true;  
                break;
            case RecordStatuses::ABORTED:
                $statusText = $messagePrefix." aborted due to time-out at ".$statusTs;            
                $reattempt = true;
                $warning = true;   
                break;
            default:
                $statusText = $messagePrefix." may have been done previously but has no status";
                $reattempt = true;
                break;
        }
        if(CLE::sys()->getAccessManager()->isAdmin()) {
            $reattempt = true;
        }
        return array($statusText, $reattempt, $warning);
    }
    function hasTransferFailed($statusTs) {
        
        $errorDt = Util::dateFromDmyHm($statusTs);
        if(!$errorDt) {
            return false;
        }
        $errorDt->add(new DateInterval('PT1H'));
        $nowDt = new DateTime();
        if($nowDt>$errorDt) {
            return true;
        }
        return false;
    }
}
