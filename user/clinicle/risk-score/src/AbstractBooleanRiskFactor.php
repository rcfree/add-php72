<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:27
 */

namespace ClinicLE\RiskScore;


use ClinicLE\RiskScore\Exception\InvalidValueException;

abstract class AbstractBooleanRiskFactor extends AbstractRiskFactor
{
    abstract function getScoreOnTrue();
    abstract function getScoreOnFalse();
    protected function doDetermineContribution(RiskScoreParams $params)
    {
        list($value) = $this
            ->getRequiredParams($params);
        if($value===true) {
            return $this->getScoreOnTrue();
        }
        if($value===false) {
            return $this->getScoreOnFalse();
        }
    }

    function validateParams(RiskScoreParams $params)
    {
        parent::validateParams($params);
        list($value) = $this
            ->getRequiredParams($params);
        if(!is_bool($value)) {
            throw new InvalidValueException(implode(",",$this->getRequiredParamNames()). " value must be true or false");
        }
    }

}