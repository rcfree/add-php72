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

class BMIRiskFactor extends AbstractRangeRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::BMI);
    }

    public function getName() {
        return "BMI";
    }

    function getContributionData()
    {
        return array(6,5,3,2,0);
    }

    function getContributionRanges()
    {
        return array(
            array("lt",15),
            array("bt",15,19),
            array("bt",20,24),
            array("bt",25,29),
            array("ge",30),
        );
    }
}