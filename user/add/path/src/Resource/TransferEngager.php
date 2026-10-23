<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/09/16
 * Time: 12:11
 */

namespace ADD\Pathology\Resource;
use CLE;
use ClinicLE\System\Util\CommandExecute;

class TransferEngager
{
    private $triggerFile;
    private $patientId;
    private $triggered = false;
    private $triggerTime;
    private $logConfigFile;

    /**
     * @return mixed
     */
    public function getTriggerTime()
    {
        return $this->triggerTime;
    }

    /**
     * @return mixed
     */
    public function getTriggerFile()
    {
        return $this->triggerFile;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patientId;
    }

    /**
     * @return mixed
     */
    public function isTriggered()
    {
        return $this->triggered;
    }

    function init($patientId, $logConfigFile) {
        if(!$patientId) {
            return;
        }
        $this->patientId = $patientId;
        $this->triggerFile = CLE::sys()->getPath()."/tmp/.path-patient-".$patientId;
        $this->logConfigFile = $logConfigFile;
    }

    public function getTransferText($triggerTime) {
        return "Pathology data transfer started at ".$triggerTime." for this patient.<br/>You may need to wait a few minutes before attempting to view it.";
    }

    public function doTransfer($forced=false) {
        $add = CLE::sys();
        $em = $add->getEncounterManager();

        $pathology = $em->domain("path",$this->patientId);
        $triggerPath = false;
        $patientId = $this->getPatientId();

        if(!$pathology->exists()) {
            $triggerPath = true;
        }
        else {
            $tests = $em->sectionByPatientAndType($patientId,"path-tests");
            if(!$tests->exists()) {
                $triggerPath=true;
            }
        }

        if($forced) {
            $triggerPath = true;
        }

        if (!$triggerPath) return null;

        $patient = CLE::db('patient')->getPatient($patientId);
        if(!$patient) {
            CLE::log($this)->error("Patient with ID ".$patientId." is not present");
        }

        $this->triggerTime = $this->getTriggerCreateTime();
        if (!$triggerPath && !$forced) {
            return $this->triggerTime;
        }
        $command = new CommandExecute("path-trans",array("-p"=>$patientId,"-w"=>""));
        $command->setProfile($add->getProfile());
        $command->setLogConfigFile($this->logConfigFile);
        $command->setAppended(true);
        $this->createPatientFile($patientId);
        
        list($output,$returnVal) = $command->executeInBackground();
        $this->triggered=true;
        $this->triggerTime = $this->getTriggerCreateTime();
    }

    function createPatientFile($patientId) {
        touch($this->triggerFile);
        chmod($this->triggerFile,0755);
    }
    function getTriggerCreateTime() {
        if(!file_exists($this->triggerFile)) return null;
        $fileTime = filectime($this->triggerFile);
        if(date("d/m/Y",$fileTime)==date("d/m/Y")) return date("H:i", $fileTime);
        return date("H:i",$fileTime)." on ".date("d/m/Y",$fileTime);
    }
}