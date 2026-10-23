<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/17
 * Time: 12:19
 */

namespace ADD\Pathology\Resource;


use ADD\Pathology\Event\MapPatientEvent;
use ADD\Pathology\Event\PathologyEvents;
use ADD\Pathology\Event\PatientPopulateEvent;
use ADD\Pathology\Event\TestItemEvent;
use ClinicLE\Common\Util;
use ClinicLE\Data\Query\EncounterCollection;
use ClinicLE\System\Service\CoreServices;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrialSupport implements EventSubscriberInterface
{
    const SNUMBER_ITEM_FIELD="SYSTEM_NUMBER";

    private $trialByIdentMap = array();
    private $identByTrialMap = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            PathologyEvents::POPULATE_PATIENTS=>"onPopulatePatients",
            PathologyEvents::RETRIEVE_TEST_ITEM=>"onRetrieveTestItem",
            PathologyEvents::MAP_PATIENT=>"onMapPatient"
        );
    }

    function __construct($trialNumber, $encType, $obsField) {
        $this->trialNumber = $trialNumber;
        $this->encType = $encType;
        $this->obsField = $obsField;
    }

    function addTrialPatient($trialNo, $patientIdent) {
        $this->identByTrialMap[$trialNo]=$patientIdent;
        $this->trialByIdentMap[$patientIdent]=$trialNo;
    }
    function getTrialNoFromIdent($ident) {
        return Util::val($ident,$this->trialByIdentMap);
    }
    function getIdentFromTrialNo($trialNo) {
        return Util::val($trialNo,$this->identByTrialMap);
    }

    function onPopulatePatients(PatientPopulateEvent $event) {
        $log = \CLE::log();
        foreach($event->getPatients() as $patient) {
            $encounters = \CLE::svc(CoreServices::ENCOUNTER_GROUP)->getAllEncountersWithPatientAndType($patient['id'],
$this->encType);

             foreach($encounters->all() as $encounter) {
                 $subjectNo = $encounter->getObsValue($this->obsField);
                 if(Util::isEmpty($subjectNo)) {
                    $log->info("Trial record in patient ".$patient['id']." is empty");
                    continue;
                 }
                 $combinedCode = $this->trialNumber.$subjectNo;

                 $log->info("Add trial no ".$combinedCode." for patient ".$patient['id']);

                $event->addPatient(array("id"=>$patient['id'],"identifier"=>$combinedCode));
                $this->addTrialPatient($combinedCode,$patient['identifier']);
             }
        }
    }
    function onRetrieveTestItem(TestItemEvent $event) {
        $testItem = $event->getTestItem();
        $possTrialNo = Util::val(self::SNUMBER_ITEM_FIELD,$testItem);

        if(!$this->isTrialNo($possTrialNo)) {
            return;
        }
        $snumber = $this->getIdentFromTrialNo($possTrialNo);
        if(!$snumber) {
            $event->stopPropagation();
            return;
        }
        $testItem[self::SNUMBER_ITEM_FIELD]=$snumber;
        $event->setTestItem($testItem);
    }
    function isTrialNo($possTrialNo) {
        return preg_match("/^T/",$possTrialNo);
    }
    function onMapPatient(MapPatientEvent $event) {
        $possTrialNo = $event->getPatientIdent();
        if(!$this->isTrialNo($possTrialNo)) {
            return;
        }
        $snumber = $this->getIdentFromTrialNo($possTrialNo);

        if(!$snumber) {
            $event->stopPropagation();
            return;
        }
        $event->setMappedPatientIdent($snumber);
    }
}