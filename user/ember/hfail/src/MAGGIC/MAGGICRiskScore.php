<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 09:39
 */

namespace EMBER\HFail\MAGGIC;

use ClinicLE\RiskScore\AbstractRiskScore;

class MAGGICRiskScore extends AbstractRiskScore
{

    function getRiskFactorList() {
        return array(
            new EjectionFractionRiskFactor(),
            new AgeRiskFactor(),
            new SystolicBpRiskFactor(),
            new BMIRiskFactor(),
            new NYHAClassRiskFactor(),
            new CreatinineRiskFactor(),
            new GeneralCombinedRiskFactor()
        );
        //"SystolicBp","BMI","Creatinine","NYHA","Male","CurrentSmoker","Diabetic","COPD","FirstDiagHF18M","NotOnBetaBlocker","NotOnACOEIOrARB");
    }

    function getName() {
        return "MAGGIC Score";
    }

}