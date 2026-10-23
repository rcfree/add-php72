<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 19/08/2016
 * Time: 16:01
 */

namespace ClinicLE\Patient\Resource;


use Symfony\Component\EventDispatcher\Event;

class PatientEvent extends Event
{
    private $patientId;

    function __construct($patientId) {
        $this->patientId = $patientId;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patientId;
    }
}