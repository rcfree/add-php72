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

abstract class AbstractTabularRangeRiskFactor extends AbstractRangeRiskFactor
{

    protected function doDetermineContribution(RiskScoreParams $params)
    {
        list($x,$y) = $this->getRequiredParams($params);
        $table = $this->getContributionData();

        $xIndex = $this->getXIndex($x);
        $yIndex = $this->getYIndex($y);
        if($xIndex===false || $yIndex===false) {
            return $this->dealWithTableOutlier($x,$y);
        }
        return $table[$yIndex][$xIndex];
    }
    function dealWithTableOutlier($x,$y) {
        throw new InvalidValueException("$x and $y were not found in the table range");
    }
    abstract function getContributionXRanges();
    abstract function getContributionYRanges();
    function getContributionRanges() {}
    function getXIndex($x) {
        return $this->getRangeIndex($x,$this->getContributionXRanges());
    }
    function getYIndex($y) {
        return $this->getRangeIndex($y,$this->getContributionYRanges());

    }
}