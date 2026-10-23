<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractBooleanRiskFactor;
use ClinicLE\RiskScore\AbstractMultiBooleanRiskFactor;

class GeneralCombinedRiskFactor extends AbstractMultiBooleanRiskFactor
{
    public function getNames() {
        return "general combined";
    }

    function getScoresOnTrue()
    {
        return array(
            MAGGICRiskFactors::MALE=>1,
            MAGGICRiskFactors::CURRENT_SMOKER=>1,
            MAGGICRiskFactors::DIABETIC=>3,
            MAGGICRiskFactors::COPD_DIAGNOSIS=>2,
            MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>2,
            MAGGICRiskFactors::NOT_BETA_BLOCKER=>3,
            MAGGICRiskFactors::NOT_ACEI_ARB=>1
        );
    }
}