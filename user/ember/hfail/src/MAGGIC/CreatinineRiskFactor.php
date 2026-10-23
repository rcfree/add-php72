<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractRangeRiskFactor;
use ClinicLE\RiskScore\AbstractRiskFactor;
use ClinicLE\RiskScore\RiskScoreParams;

class CreatinineRiskFactor extends AbstractRangeRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::CREATININE);
    }

    public function getName() {
        return "creatinine";
    }

    function getContributionData()
    {
        return array(0,1,2,3,4,5,6,8);
    }

    function getContributionRanges()
    {
        return array(
            array("lt",90),
            array("bt",90,109),
            array("bt",110,129),
            array("bt",130,149),
            array("bt",150,169),
            array("bt",170,209),
            array("bt",210,249),
            array("ge",250)
        );
    }
}