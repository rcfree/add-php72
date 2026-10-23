<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 11:03
 */

namespace EMBER\HFail\Test\MAGGIC\MAGGICRiskScore;


abstract class AbstractMAGGICTest extends \PHPUnit_Framework_TestCase
{
    public function buildScoreProvider($scoreData, $name1,$name2=null) {

        $provider = array();
        foreach($scoreData as $value) {
            if($name2) {
                $provider["score_".$value[0]."_if_{$name1}_".$value[1]."_and_{$name2}_".$value[2]]=$value;
            }
            else {
                if(is_array($value[1])) {
                    $provider["score_".$value[0]."_if_{$name1}_".implode("_and_",array_map(function($key) use($value) {
                        return $key."=".$value[1][$key];
                    },array_keys($value[1])))]=$value;
                }
                else {
                    $provider["score_".$value[0]."_if_{$name1}_".$value[1]]=$value;
                }
            }
        }

        return $provider;
    }
}