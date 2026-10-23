<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractTabularRangeRiskFactor;

class SystolicBpRiskFactor extends AbstractTabularRangeRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::SYSTOLIC_BP,MAGGICRiskFactors::EJECTION_FRACTION);
    }

    function getContributionData() {
        return array(
            array(5,4,3,2,1,0),
            array(3,2,1,1,0,0),
            array(2,1,1,0,0,0)
        );
    }

    function getContributionXRanges() {
        return array(
            array("lt",110),
            array("bt",110,119),
            array("bt",120,129),
            array("bt",130,139),
            array("bt",140,149),
            array("ge",150)
        );
    }
    function getContributionYRanges() {
        return array(
            array("lt",30),
            array("bt",30,39),
            array("ge",40)
        );
    }

    public function getName() {
        return "systolic bp";
    }

}