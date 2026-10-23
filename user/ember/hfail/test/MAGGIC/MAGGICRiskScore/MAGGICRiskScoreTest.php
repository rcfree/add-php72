<?php
namespace EMBER\HFail\Test\MAGGIC\MAGGICRiskScore;
use ClinicLE\RiskScore\RiskScoreParams;
use EMBER\HFail\MAGGIC\MAGGICRiskScore;
use EMBER\HFail\MAGGIC\MAGGICRiskFactors;

class MAGGICRiskScoreTest extends AbstractMAGGICTest {
    private $SUT;

    function setUp() {
        $this->SUT = new MAGGICRiskScore();
    }

    /**
     * @test
     * @dataProvider exampleParameters
     *
     */
    public function example_parameters_work_for_various_values($expContrib, $params) {
        $riskScoreParams = new RiskScoreParams($params);
        $this->assertEquals($expContrib,$this->SUT->calculateScore($riskScoreParams));
    }

    public function exampleParameters() {
        return $this->buildScoreProvider(
            array(
                array((7+10+5+6+8+8+13),array(
                    MAGGICRiskFactors::EJECTION_FRACTION=>19.6,
                    MAGGICRiskFactors::AGE=>82,
                    MAGGICRiskFactors::SYSTOLIC_BP=>100,
                    MAGGICRiskFactors::BMI=>14,
                    MAGGICRiskFactors::CREATININE=>260,
                    MAGGICRiskFactors::NYHA_CLASS=>4,
                    MAGGICRiskFactors::MALE=>true,
                    MAGGICRiskFactors::CURRENT_SMOKER=>true,
                    MAGGICRiskFactors::DIABETIC=>true,
                    MAGGICRiskFactors::COPD_DIAGNOSIS=>true,
                    MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>true,
                    MAGGICRiskFactors::NOT_BETA_BLOCKER=>true,
                    MAGGICRiskFactors::NOT_ACEI_ARB=>true
                )),
                array((7+10+5+6+8+13),array(
                    MAGGICRiskFactors::EJECTION_FRACTION=>19,
                    MAGGICRiskFactors::AGE=>82,
                    MAGGICRiskFactors::SYSTOLIC_BP=>100,
                    MAGGICRiskFactors::BMI=>14,
                    MAGGICRiskFactors::CREATININE=>89,
                    MAGGICRiskFactors::NYHA_CLASS=>4,
                    MAGGICRiskFactors::MALE=>true,
                    MAGGICRiskFactors::CURRENT_SMOKER=>true,
                    MAGGICRiskFactors::DIABETIC=>true,
                    MAGGICRiskFactors::COPD_DIAGNOSIS=>true,
                    MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>true,
                    MAGGICRiskFactors::NOT_BETA_BLOCKER=>true,
                    MAGGICRiskFactors::NOT_ACEI_ARB=>true
                )),
                array((15+2+6+8+13),array(
                    MAGGICRiskFactors::EJECTION_FRACTION=>40,
                    MAGGICRiskFactors::AGE=>82,
                    MAGGICRiskFactors::SYSTOLIC_BP=>100,
                    MAGGICRiskFactors::BMI=>14,
                    MAGGICRiskFactors::CREATININE=>89,
                    MAGGICRiskFactors::NYHA_CLASS=>4,
                    MAGGICRiskFactors::MALE=>true,
                    MAGGICRiskFactors::CURRENT_SMOKER=>true,
                    MAGGICRiskFactors::DIABETIC=>true,
                    MAGGICRiskFactors::COPD_DIAGNOSIS=>true,
                    MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>true,
                    MAGGICRiskFactors::NOT_BETA_BLOCKER=>true,
                    MAGGICRiskFactors::NOT_ACEI_ARB=>true
                )),
                    array(0,array(
                        MAGGICRiskFactors::EJECTION_FRACTION=>40,
                        MAGGICRiskFactors::AGE=>23,
                        MAGGICRiskFactors::SYSTOLIC_BP=>150,
                        MAGGICRiskFactors::BMI=>32,
                        MAGGICRiskFactors::CREATININE=>89,
                        MAGGICRiskFactors::NYHA_CLASS=>1,
                        MAGGICRiskFactors::MALE=>false,
                        MAGGICRiskFactors::CURRENT_SMOKER=>false,
                        MAGGICRiskFactors::DIABETIC=>false,
                        MAGGICRiskFactors::COPD_DIAGNOSIS=>false,
                        MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>false,
                        MAGGICRiskFactors::NOT_BETA_BLOCKER=>false,
                        MAGGICRiskFactors::NOT_ACEI_ARB=>false
                    )),
        ),"");

    }
}