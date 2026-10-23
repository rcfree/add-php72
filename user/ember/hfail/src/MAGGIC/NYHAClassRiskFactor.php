<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractCategoricalRiskFactor;
use ClinicLE\RiskScore\AbstractRangeRiskFactor;
use ClinicLE\RiskScore\AbstractRiskFactor;
use ClinicLE\RiskScore\RiskScoreParams;

class NYHAClassRiskFactor extends AbstractCategoricalRiskFactor
{
    function getRequiredParamNames() {
        return array(MAGGICRiskFactors::NYHA_CLASS);
    }

    public function getName() {
        return "NYHA class";
    }

    function getContributionData()
    {
        return array(1=>0,2=>2,3=>6,4=>8);
    }

}