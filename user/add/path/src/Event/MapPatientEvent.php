<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/17
 * Time: 13:01
 */

namespace ADD\Pathology\Event;


use Symfony\Component\EventDispatcher\Event;

class MapPatientEvent extends Event
{
    private $patientIdent;
    private $mappedPatientIdent;
    function __construct($patientIdent) {
        $this->patientIdent = $patientIdent;
        $this->mappedPatientIdent = $patientIdent;
    }

    /**
     * @return mixed
     */
    public function getMappedPatientIdent()
    {
        return $this->mappedPatientIdent;
    }

    /**
     * @param mixed $mappedPatientIdent
     */
    public function setMappedPatientIdent($mappedPatientIdent)
    {
        $this->mappedPatientIdent = $mappedPatientIdent;
    }

    /**
     * @return mixed
     */
    public function getPatientIdent()
    {
        return $this->patientIdent;
    }

}