<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:37
 */

namespace ClinicLE\RiskScore;


use ClinicLE\RiskScore\AbstractRiskScoreParams;

class RiskScoreBuilder
{
    function __construct(RiskFactorFactory $riskFactorFactory,RiskScoreParams $riskScoreParams) {
        $this->riskFactorFactory = $riskFactorFactory;
        $this->riskScoreParams = $riskScoreParams;
    }
    function buildAndValidate($className) {
        $riskScore = $className->new($this->riskScoreParams);


    }
}