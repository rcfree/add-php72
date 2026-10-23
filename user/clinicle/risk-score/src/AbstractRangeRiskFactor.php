<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace ClinicLE\RiskScore;


use ClinicLE\RiskScore\Exception\InvalidValueException;
use ClinicLE\RiskScore\Exception\RangeNotFoundException;

abstract class AbstractRangeRiskFactor extends AbstractRiskFactor implements DataDrivenRiskFactorInterface
{
    protected function doDetermineContribution(RiskScoreParams $params)
    {
        list($value) = $this
            ->getRequiredParams($params);
        $data = $this->getContributionData();

        $index = $this->getRangeIndex($value, $this->getContributionRanges());
        if($index===false) {
            return $this->dealWithRangeOutlier($value);
        }
        return $data[$index];
    }
    abstract function getContributionRanges();
    function dealWithRangeOutlier($x) {
        throw new InvalidValueException($this->getName()." value of $x is not in the specified ranges for the ".$this->getRiskScore()->getName());
    }

    function getRangeIndex($value,$ranges) {
        $index=0;
        foreach($ranges as $range) {
            $op = $range[0];
            if($op=="lt" && $value < $range[1]) {
                return $index;
            }
            if($op=="le" && $value <= $range[1]) {
                return $index;
            }
            if($op=="ge" && $value >= $range[1]) {
                return $index;
            }
            if($op=="gt" && $value > $range[1]) {
                return $index;
            }
            if($op=="bt" && ($value >= $range[1] && $value<=$range[2])) {
                return $index;
            }
            $index++;
        }
        return false;
    }
}