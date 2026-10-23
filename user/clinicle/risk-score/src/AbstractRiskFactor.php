<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:27
 */

namespace ClinicLE\RiskScore;


use ClinicLE\Common\Exception\SettingNotDefinedException;
use ClinicLE\Common\Util;
use ClinicLE\RiskScore\RiskScoreParams;

abstract class AbstractRiskFactor
{
    private $riskScore;
    private $params;

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    function setRiskScore(AbstractRiskScore $riskScore) {
        $this->riskScore = $riskScore;
    }

    /**
     * @return AbstractRiskScore
     */
    public function getRiskScore()
    {
        return $this->riskScore;
    }

    abstract function getRequiredParamNames();
    function getRequiredParams(RiskScoreParams $params, $sequential=true) {
        $allParams = $params->all();
        if($sequential) {
            return Util::vals($this->getRequiredParamNames(),$allParams);
        }
        $requiredParams = array();

        foreach($this->getRequiredParamNames() as $requiredParamName) {
            $requiredParams[$requiredParamName]=$allParams[$requiredParamName];
        }
        return $requiredParams;
    }
    abstract protected function doDetermineContribution(RiskScoreParams $params);
    function determineContribution(RiskScoreParams $params) {
        $this->validateParams($params);
        return $this->doDetermineContribution($params);
    }
    function getName() {
        return "untitled";
    }
    function getType() {
        return get_class($this);
    }
    function validateParams(RiskScoreParams $params) {
        foreach ($this->getRequiredParamNames() as $requiredParam) {
            if ($params->has($requiredParam)) {
                continue;
            }
            throw new SettingNotDefinedException("'$requiredParam' parameter is required to calculate the " . $this->getName() . " risk factor in the " . $this->getRiskScore()->getName() . " score");
        }
    }
}