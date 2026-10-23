<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:10
 */

namespace ClinicLE\RiskScore;


use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Common\Exception\SettingNotDefinedException;
use ClinicLE\RiskScore\Exception\RiskFactorMissingException;
use ClinicLE\RiskScore\RiskScoreParams;

abstract class AbstractRiskScore
{
    private $riskFactors;
    private $riskFactorTypes;

    function __construct()
    {
        foreach($this->getRiskFactorList() as $riskFactor) {
            if(!is_a($riskFactor,AbstractRiskFactor::class)) {
                throw new \InvalidArgumentException("Risk factor ".get_class($riskFactor)." is not an AbstractRiskFactor");
            }
            $this->addRiskFactor($riskFactor);
        }
    }

    public function calculateScore(RiskScoreParams $params) {
        $score = $this->getInitialScore();
        foreach($this->riskFactors as $riskFactor) {
            $this->validateRiskFactor($riskFactor,$params);
            $score+=$riskFactor->determineContribution($params);
        }
        return $score;
    }
    public function getInitialScore() {
        return 0;
    }
    function addRiskFactor(AbstractRiskFactor $riskFactor) {
        $riskFactor->setRiskScore($this);

        $this->riskFactors[]=$riskFactor;
        $this->riskFactorTypes[$riskFactor->getType()]=count($this->riskFactors)-1;
    }

    /**
     * @param $riskFactorType
     * @return AbstractRiskFactor
     * @throws RiskFactorMissingException
     */
    function getRiskFactor($riskFactorType) {
        if(!isset($this->riskFactorTypes[$riskFactorType])) {
            throw new RiskFactorMissingException($riskFactorType." is not a risk factor in the ".$this->getName());
        }
        return $this->riskFactors[$this->riskFactorTypes[$riskFactorType]];
    }
    function validateRiskFactor(AbstractRiskFactor $riskFactor, RiskScoreParams $params) {
        foreach ($riskFactor->getRequiredParamNames() as $requiredParam) {
            if ($params->has($requiredParam)) {
                continue;
            }
            throw new SettingNotDefinedException("'$requiredParam' parameter is required to calculate the " . $riskFactor->getName() . " factor of the " . $this->getName() . " score");
        }
        $riskFactor->validateParams($params);

    }
    abstract function getName();
}