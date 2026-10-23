<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 12:09
 */

namespace ClinicLE\Data\PageFieldSearch;


use ClinicLE\Common\Util;
use ClinicLE\System\Service\CoreServices;

abstract class AbstractPageFieldSearch
{
    private $message;
    private $values=array();
    private $ignoreMissing=false;
    private $pim;
    private $creator;

    /**
     * @return PageFieldSearchCreator
     */
    public function getCreator()
    {
        return $this->creator;
    }


    function __construct($pim, PageFieldSearchCreator $creator) {
        $this->pim = $pim;
        $this->creator = $creator;
    }
    /**
     * @return mixed
     */
    public function getPim()
    {
        return $this->pim;
    }

    /**
     * @param mixed $pim
     */
    public function setPim($pim)
    {
        $this->pim = $pim;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return bool
     */
    public function isIgnoreMissing()
    {
        return $this->ignoreMissing;
    }

    /**
     * @param bool $ignoreMissing
     */
    public function setIgnoreMissing($ignoreMissing)
    {
        $this->ignoreMissing = $ignoreMissing;
    }

    function getPageObservations($page, $encounterRecord, $obsNames) {
        $form = $page->getComponent('Form');
        $form->setup();

        $outputObs = array();
        $missingObs = array();
        foreach($obsNames as $obsName) {
            $obsValue = $this->getObservation($obsName, $encounterRecord);
            if(Util::isEmpty($obsValue)) {
                $of = $form->getField($obsName);

                if (!$of) throw new \ADD_FieldException("Field '$obsName' not found in page '".$page->getName()."'");
                $missingObs[]=$of->getTitle();
            }
            else {
                $outputObs[]=$obsValue;
            }

        }
        if (count($missingObs)>0 && !$this->isIgnoreMissing()) {
            $this->getCreator()->addWarning($this->getMissingObsWarningMessage($missingObs,$page));
            return null;
        }

        return $outputObs;
    }
    abstract function getObservation($obsName, $encounterRecord);

    abstract function findObservations($obsNames);
    function findObservation($obsName) {
        $pageObservations = $this->findObservations(array($obsName));
        return $pageObservations[0];
    }
    function findLinkedObservation($obsContainingId, $encTypeToRetrieve, $obsName) {
        $encIdToRetrieve = $this->findObservation($obsContainingId);
        $record = \CLE::svc(CoreServices::ENCOUNTER_GROUP)->getSingleEncounterWithPatientTypeAndId($this->getCreator()->getPatientId(), $encTypeToRetrieve, $encIdToRetrieve);
        \CLE::info("record",$record);
        return $record->getObsValue($obsName);
    }

}