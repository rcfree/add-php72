<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractBooleanRiskFactor;

class MaleRiskFactor extends AbstractBooleanRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::MALE);
    }

    public function getName() {
        return "male";
    }

    function getScoreOnTrue()
    {
        return 1;
    }

    function getScoreOnFalse()
    {
        return 0;
    }


}