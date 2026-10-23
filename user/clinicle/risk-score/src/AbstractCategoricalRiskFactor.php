<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:18
 */

namespace ClinicLE\RiskScore;


use ClinicLE\RiskScore\Exception\InvalidValueException;

abstract class AbstractCategoricalRiskFactor extends AbstractRiskFactor implements DataDrivenRiskFactorInterface
{
    protected function doDetermineContribution(RiskScoreParams $params)
    {
        list($value) = $this
            ->getRequiredParams($params);
        $data = $this->getContributionData();

        if(!array_key_exists($value,$data)) {
            return $this->dealWithCategoryOutlier($value);
        }
        return $data[$value];
    }

    function dealWithCategoryOutlier($x) {
        throw new InvalidValueException("$x is not a category");
    }

}