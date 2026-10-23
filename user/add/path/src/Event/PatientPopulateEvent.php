<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/08/2017
 * Time: 17:31
 */

namespace ADD\Pathology\Event;
use Symfony\Component\EventDispatcher\Event;

class PatientPopulateEvent extends Event
{
    private $patients=array();

    /**
     * @return array
     */
    public function getPatients() {
        return $this->patients;
    }

    /**
     * @param array $patients
     */
    public function setPatients($patients) {
        $this->patients = $patients;
    }

    public function addPatient($patient) {
        $this->patients[]=$patient;
    }

}