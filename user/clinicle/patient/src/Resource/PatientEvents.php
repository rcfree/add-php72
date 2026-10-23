<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 19/08/2016
 * Time: 16:00
 */

namespace ClinicLE\Patient\Resource;


class PatientEvents
{
    const BEFORE_DELETE_PATIENT="patientEvents.beforeDeletePatient";
    const BEFORE_PURGE_PATIENT="patientEvents.beforePurgePatient";
}