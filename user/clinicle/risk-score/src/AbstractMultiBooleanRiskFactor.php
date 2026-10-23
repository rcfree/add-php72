<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:27
 */

namespace ClinicLE\RiskScore;


use ClinicLE\RiskScore\Exception\InvalidValueException;

abstract class AbstractMultiBooleanRiskFactor extends AbstractRiskFactor
{
    abstract function getScoresOnTrue();

    function getScoresOnFalse() {
        return array();
    }

    protected function doDetermineContribution(RiskScoreParams $params)
    {
        $requiredParams = $this
            ->getRequiredParams($params,false);

        $trueScores = $this->getScoresOnTrue();
        $falseScores = $this->getScoresOnFalse();
        $score = $this->getInitialScore();

        foreach($requiredParams as $name => $value) {
            if ($value === true) {
                $score+=isset($trueScores[$name]) ? $trueScores[$name] : 0;
            }
            if ($value === false) {
                $score+=isset($falseScores[$name]) ? $falseScores[$name] : 0;
            }
        }
        return $score;
    }

    function validateParams(RiskScoreParams $params)
    {
        parent::validateParams($params);

        $requiredParams = $this
            ->getRequiredParams($params,false);


        foreach($requiredParams as $name => $requiredParam) {
            if (!is_bool($requiredParam)) {
                throw new InvalidValueException($name." value must be true or false");
            }
        }
    }

    function getRequiredParamNames()
    {
        return array_keys($this->getScoresOnTrue());
    }
    function getInitialScore() {
        return 0;
    }
}