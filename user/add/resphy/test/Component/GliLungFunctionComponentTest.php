<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 28/05/19
 * Time: 12:05
 */
use ADD\Resphy\GliLungFunctionCalculator;
class GliLungFunctionComponentTest extends \PHPUnit_Framework_TestCase
{
    static $rPackagePath = "/local/rpackages";
    function test_male_height1_7_age56_eth1_is_calculated() {
        $calc = new GliLungFunctionCalculator("M",170,56,1, self::$rPackagePath);
        $values = $calc->calculatePredValues();
        $this->assertEquals($values,array(
            "LungFunction.pred_fev1"=>"3.38",
            "LungFunction.pred_fvc"=>"4.30",
            "LungFunction.pred_fev1_fvc"=>"0.79",
            "LungFunction.pred_pef"=>""
        ));
    }

    function test_female_height1_7_age56_eth1_is_calculated() {
        $calc = new GliLungFunctionCalculator("F",170,56,1, self::$rPackagePath);
        $values = $calc->calculatePredValues();
        $this->assertEquals($values,array(
            "LungFunction.pred_fev1"=>"2.87",
            "LungFunction.pred_fvc"=>"3.64",
            "LungFunction.pred_fev1_fvc"=>"0.79",
            "LungFunction.pred_pef"=>""
        ));
    }

    function test_female_height1_2_age4_eth3_is_calculated() {
        $calc = new GliLungFunctionCalculator("F",120,4,3, self::$rPackagePath);
        $values = $calc->calculatePredValues();
        $this->assertEquals($values,array(
            "LungFunction.pred_fev1"=>"1.25",
            "LungFunction.pred_fvc"=>"1.33",
            "LungFunction.pred_fev1_fvc"=>"0.94",
            "LungFunction.pred_pef"=>""
        ));
    }

    /**
     * @expectedException \ADD\Resphy\GliLungFunctionException
     */
    function test_male_height1_5_age105_eth1_fails_due_to_gt_95_years() {
        $calc = new GliLungFunctionCalculator("M",150,105,1, self::$rPackagePath);
        $values = $calc->calculatePredValues();

    }

    function test_male_height1_5_age95_is_calculated_eth1() {
        $calc = new GliLungFunctionCalculator("M",150,95,1, self::$rPackagePath);
        $values = $calc->calculatePredValues();
        $this->assertEquals($values,array(
            "LungFunction.pred_fev1"=>"1.70",
            "LungFunction.pred_fvc"=>"2.29",
            "LungFunction.pred_fev1_fvc"=>"0.75",
            "LungFunction.pred_pef"=>""
        ));
    }

    /**
     * @expectedException \ADD\Resphy\GliLungFunctionException
     */
    function test_male_height1_5_age95_eth6_fails_due_to_incorrect_eth() {
        $calc = new GliLungFunctionCalculator("M",150,95,6, self::$rPackagePath);
        $values = $calc->calculatePredValues();

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function test_male_height1_5_age95_eth_null_fails_due_to_null_value() {
        $calc = new GliLungFunctionCalculator("M",150,95,null, self::$rPackagePath);
        $values = $calc->calculatePredValues();

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function test_male_height_null_age95_eth4_fails_due_to_null_value() {
        $calc = new GliLungFunctionCalculator("M",null,95,4, self::$rPackagePath);
        $values = $calc->calculatePredValues();

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function test_male_height1_6_null_age_eth4_fails_due_to_null_value() {
        $calc = new GliLungFunctionCalculator("M",160,null,4, self::$rPackagePath);
        $values = $calc->calculatePredValues();
    }
}