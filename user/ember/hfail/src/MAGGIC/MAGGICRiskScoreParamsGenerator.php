<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 11:56
 */

namespace EMBER\HFail\MAGGIC;


use ClinicLE\Data\PageFieldSearch\AbstractPageFieldSearch;
use ClinicLE\Data\PageFieldSearch\PageFieldSearchCreator;
use ClinicLE\Data\Util\PageDataSearch;
use ClinicLE\RiskScore\RiskScoreParams;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Common\Util;

class MAGGICRiskScoreParamsGenerator
{
    private $creator;

    function __construct(PageFieldSearchCreator $creator)
    {
        $this->creator = $creator;
    }
    function createParams() {
        $patient = \CLE::db('patient')->getPatient($this->creator->getPatientId());

        $demogPageSearch = $this->creator->createPageSearch("ademog");
        $adultPageSearch = $this->creator->createPageSearch("aadultspec");
        $hfailPageSearch = $this->creator->createPageSearch("hadmission");

        $params = array(
            MAGGICRiskFactors::AGE=>$this->getPatientAge($patient, $demogPageSearch),

            MAGGICRiskFactors::MALE=>$this->isPatientMale($patient),

            MAGGICRiskFactors::BMI=>$demogPageSearch->findObservation("bmi"),
            MAGGICRiskFactors::SYSTOLIC_BP=>$demogPageSearch->findObservation("bp_top"),

            MAGGICRiskFactors::CREATININE=>$this->creator->createPageSearch("ablood")->findObservation("biochem_value_crea"),

            MAGGICRiskFactors::NYHA_CLASS=>$hfailPageSearch->findObservation("nyha_class"),
            MAGGICRiskFactors::EJECTION_FRACTION=>$adultPageSearch->findObservation("echo_ef"),

            MAGGICRiskFactors::CURRENT_SMOKER=>$this->isCurrentSmoker($demogPageSearch),
            MAGGICRiskFactors::DIABETIC=>$this->isYesValue($hfailPageSearch,"diabetes"),
            MAGGICRiskFactors::COPD_DIAGNOSIS=>$this->isYesValue($hfailPageSearch,"copd"),
            MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>$this->isYesValue($hfailPageSearch, "first_diag_hf_lt18m"),
            MAGGICRiskFactors::NOT_BETA_BLOCKER=>(!$this->isYesValue($hfailPageSearch, "on_beta_blocker")),
            MAGGICRiskFactors::NOT_ACEI_ARB=>(!$this->isYesValue($hfailPageSearch, "on_acei_arb"))
        );
        $params = $this->roundSelectedParams($params,array(
            MAGGICRiskFactors::AGE,
            MAGGICRiskFactors::BMI,
            MAGGICRiskFactors::SYSTOLIC_BP,
            MAGGICRiskFactors::CREATININE,
            MAGGICRiskFactors::EJECTION_FRACTION
        ));
        return new RiskScoreParams($params);
    }
    
    function roundSelectedParams($params, $riskFactors) {
        foreach($riskFactors as $riskFactor) {
            if(!isset($params[$riskFactor])) {
                continue;
            }
            $params[$riskFactor] = round($params[$riskFactor]);
        }
        return $params;
    }
    function getPatientAge($patient, $demogPageSearch)
    {
        list($enrolDate) = $demogPageSearch->findObservations(array("enrolled_date"));
        $patientAge = \ADD_Field_Util::age($patient['date_of_birth'],$enrolDate);
        return $patientAge;
    }
    function isPatientMale($patient) {
        return Util::val("gender",$patient)=="M";
    }
    function isCurrentSmoker($demogPageSearch) {
        $smokingStatus = $demogPageSearch->findLinkedObservation("link_smoking","history-other-smoke","smoking_status");

       return $smokingStatus==2;
    }
    function isYesValue(AbstractPageFieldSearch $pageSearch,$obsName) {
        $obs = $pageSearch->findObservation($obsName);
        return $obs=="Y";
    }
}
