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

class EjectionFractionRiskFactor extends AbstractRangeRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::EJECTION_FRACTION);
    }

    public function getName() {
        return "ejection fraction";
    }

    function getContributionData()
    {
        return array(7,6,5,3,2,0);
    }

    function getContributionRanges()
    {
        return array(
            array("lt",20),
            array("bt",20,24),
            array("bt",25,29),
            array("bt",30,34),
            array("bt",35,39),
            array("ge",40),
        );
    }
}