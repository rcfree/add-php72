<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractTabularRangeRiskFactor;

class AgeRiskFactor extends AbstractTabularRangeRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::AGE,MAGGICRiskFactors::EJECTION_FRACTION);
    }

    function getContributionData() {
        return array(
            array(0,1,2,4,6,8,10),
            array(0,2,4,6,8,10,13),
            array(0,3,5,7,9,12,15)
        );
    }

    function getContributionXRanges() {
        return array(
            array("le",55),
            array("bt",56,59),
            array("bt",60,64),
            array("bt",65,69),
            array("bt",70,74),
            array("bt",75,79),
            array("ge",80)
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
        return "age";
    }

}