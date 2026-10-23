<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 03/01/17
 * Time: 14:06
 */

namespace ADD\Qol\Test;


use ADD\Qol\AjaxFunction\AcqIaScore7AjaxFunction;
use ClinicLE\Testing\AbstractBaseTest;
use Symfony\Component\HttpFoundation\Request;

class BckqScoreAjaxFunctionTest extends AbstractBaseTest 
{

    function setup() {
        $sys = $this->initMockSystem();
        $sys->initServiceContainer();
        $this->callingPage = $this->getMockBuilder('\ADD_Page')->disableOriginalConstructor()->getMock();
        $formFile = "qol-acqia.ftx";

        $this->callingPage->expects($this->any())->method("getFilePath")->will($this->returnValue
        (__DIR__."/../"));
        $this->callingPage->expects($this->any())->method("getSetting")->with("form")->will($this->returnValue
        ($formFile));
        $callingForm = new \ADD_Component_Form_File($this->callingPage);
        $this->callingPage->expects($this->any())->method("getComponent")->with("Form")->will($this->returnValue
        ($callingForm));
        $this->SUT = new AcqIaScore7AjaxFunction($this->callingPage);
    }

    /**
     * @test
     */
    function empty_answers_calculates_blank_score() {
        $request = new Request(array());
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>"","fev1_pc_predicted"=>""),$results);
    }

    /**
     * @test
     */
    function text_values_calculates_blank_score() {
        $request = new Request(array("fev1_pre_bronch"=>"TEXT","fev1_predicted"=>"TEXT"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>"","fev1_pc_predicted"=>""),$results);
    }

    /**
     * @test
     */
    function invalid_values_calculates_blank_score() {
        $request = new Request(array("fev1_pre_bronch"=>"32","fev1_predicted"=>"0"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>"","fev1_pc_predicted"=>""),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_pc_lt_50_returns_6() {
        $request = new Request(array("fev1_pre_bronch"=>"1.1","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"6"),"fev1_pc_predicted"=>array("value"=>"44.0")),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_pc_bt_50_59_returns_5() {
        $request = new Request(array("fev1_pre_bronch"=>"1.3","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"5"),"fev1_pc_predicted"=>array("value"=>"52.0")),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_pc_bt_60_69_returns_4() {
        $request = new Request(array("fev1_pre_bronch"=>"1.5","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"4"),"fev1_pc_predicted"=>array("value"=>"60.0")),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_pc_bt_70_79_returns_3() {
        $request = new Request(array("fev1_pre_bronch"=>"1.8","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"3"),"fev1_pc_predicted"=>array("value"=>"72.0")),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_pc_bt_80_89_returns_2() {
        $request = new Request(array("fev1_pre_bronch"=>"2.0","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"2"),"fev1_pc_predicted"=>array("value"=>"80.0")),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_pc_bt_90_95_returns_1() {
        $request = new Request(array("fev1_pre_bronch"=>"2.3","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"1"),"fev1_pc_predicted"=>array("value"=>"92.0")),$results);
    }

    /**
     * @test
     */
    function bronch_predicted_gt_95_returns_0() {
        $request = new Request(array("fev1_pre_bronch"=>"2.45","fev1_predicted"=>"2.5"));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score_7"=>array("value"=>"0"),"fev1_pc_predicted"=>array("value"=>"98.0")),$results);
    }

}