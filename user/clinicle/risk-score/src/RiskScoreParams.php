<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/10/17
 * Time: 10:12
 */

namespace ClinicLE\RiskScore;


use ClinicLE\Common\Config\ArrayConfig;

class RiskScoreParams extends ArrayConfig
{
    /*
    function __construct($params) {
        parent::__construct($params);
        foreach($this->getRequiredParams() as $requiredParam) {
            if($this->has($requiredParam)) {
                continue;
            }
            throw new SettingNotDefinedException("'$requiredParam' parameter is required to calculate the ".$this->getRiskScoreName());
        }
    }
    */
}