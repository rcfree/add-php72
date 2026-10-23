<?php
namespace EMBER\HFail\Test\MAGGIC\MAGGICRiskScore;
use ClinicLE\RiskScore\RiskScoreParams;
use EMBER\HFail\MAGGIC\AgeRiskFactor;
use EMBER\HFail\MAGGIC\BMIRiskFactor;
use EMBER\HFail\MAGGIC\CreatinineRiskFactor;
use EMBER\HFail\MAGGIC\EjectionFractionRiskFactor;
use EMBER\HFail\MAGGIC\GeneralCombinedRiskFactor;
use EMBER\HFail\MAGGIC\MAGGICRiskScore;
use EMBER\HFail\MAGGIC\NYHAClassRiskFactor;
use EMBER\HFail\MAGGIC\SystolicBpRiskFactor;
use EMBER\HFail\MAGGIC\MAGGICRiskFactors;

class MAGGICRiskFactorTest extends AbstractMAGGICTest {
    private $SUT;

    function setUp() {
        $this->SUT = new MAGGICRiskScore();
    }
    /**
	 * @test
     * @expectedException ClinicLE\Common\Exception\SettingNotDefinedException
	 */
	 public function age_score_fails_without_age() {
         $riskFactor = $this->SUT->getRiskFactor(AgeRiskFactor::class);
	     $this->assertEquals(0,$riskFactor->determineContribution(
             new RiskScoreParams(array(
                     MAGGICRiskFactors::AGE=>29
                 )
             )));
	 }

    /**
     * @test
     * @expectedException ClinicLE\Common\Exception\SettingNotDefinedException
     */
    public function age_score_fails_without_ef() {
        $riskFactor = $this->SUT->getRiskFactor(AgeRiskFactor::class);
        $this->assertEquals(0,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::AGE=>29

                )
            )));
    }

    /**
     * @test
     * @expectedException ClinicLE\Common\Exception\SettingNotDefinedException
     */
    public function age_score_fails_with_non_range_age() {
        $riskFactor = $this->SUT->getRiskFactor(AgeRiskFactor::class);
        $this->assertEquals(0,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::AGE=>0,
                    MAGGICRiskFactors::EJECTION_FRACTION=>null
                )
            )));
    }

    /**
     * @test
     * @dataProvider ageScoreValues
     *
     */
    public function age_score_works_for_various_values($expContrib, $age, $ef) {
        $riskFactor = $this->SUT->getRiskFactor(AgeRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::AGE=>$age,
                    MAGGICRiskFactors::EJECTION_FRACTION=>$ef
                )
            )));
    }

    /**
     * @test
     * @dataProvider efScoreValues
     *
     */
    public function ef_score_works_for_various_values($expContrib, $ef) {
        $riskFactor = $this->SUT->getRiskFactor(EjectionFractionRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::EJECTION_FRACTION=>$ef
                )
            )));
    }

    /**
     * @test
     * @dataProvider systBpScoreValues
     *
     */
    public function systBp_score_works_for_various_values($expContrib, $systBp, $ef) {
        $riskFactor = $this->SUT->getRiskFactor(SystolicBpRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::EJECTION_FRACTION=>$ef,
                    MAGGICRiskFactors::SYSTOLIC_BP=>$systBp
                )
            )));
    }
    /**
     * @test
     * @dataProvider bmiScoreValues
     *
     */
    public function bmi_score_works_for_various_values($expContrib, $bmi) {
        $riskFactor = $this->SUT->getRiskFactor(BMIRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::BMI=>$bmi
                )
            )));
    }

    /**
     * @test
     * @dataProvider nyhaClassValues
     *
     */
    public function nyha_class_works_for_various_values($expContrib, $nyhaClass) {
        $riskFactor = $this->SUT->getRiskFactor(NYHAClassRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::NYHA_CLASS=>$nyhaClass
                )
            )));
    }

    /**
     * @test
     * @dataProvider creatinineValues
     *
     */
    public function creatinine_works_for_various_values($expContrib, $creatinine) {
        $riskFactor = $this->SUT->getRiskFactor(CreatinineRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams(array(
                    MAGGICRiskFactors::CREATININE=>$creatinine
                )
            )));
    }

    /**
     * @test
     * @dataProvider combinedValues
     *
     */
    public function combined_scores_work_for_various_values($expContrib, $params) {
        $riskFactor = $this->SUT->getRiskFactor(GeneralCombinedRiskFactor::class);
        $this->assertEquals($expContrib,$riskFactor->determineContribution(
            new RiskScoreParams($params)));
    }

    public function systBpScoreValues() {
        $scoreData = array(
            array(5,109,29),
            array(4,112,29),
            array(2,138,27),
            array(0,150,16),
            array(2,116,37),
            array(1,139,30),
            array(0,157,39),
            array(2,109,40),
            array(1,125,48),
            array(0,170,47)
        );
        return $this->buildScoreProvider($scoreData, "systolic_bp","ef");

    }

    public function efScoreValues() {
        return $this->buildScoreProvider(array(
            array(7,18),
            array(6,22),
            array(5,28),
            array(3,30),
            array(2,38),
            array(0,40)
            )
        ,"ef");
    }
    public function bmiScoreValues() {
        return $this->buildScoreProvider(array(
                array(6,14),
                array(5,15),
                array(3,20),
                array(2,26),
                array(0,30)
            )
            ,"bmi");
    }
    public function creatinineValues() {
        return $this->buildScoreProvider(array(
                array(0,89),
                array(1,90),
                array(2,110),
                array(3,130),
                array(4,150),
                array(5,170),
                array(6,210),
                array(8,250)
            )
            ,"creatinine");
    }
    public function nyhaClassValues() {
        return $this->buildScoreProvider(array(
                array(0,1),
                array(2,2),
                array(6,3),
                array(8,4)
            )
            ,"nyha_class");
    }
    public function ageScoreValues() {
        return $this->buildScoreProvider(array(
            array(0,55,29),
            array(2,62,29),
            array(4,68,29),
            array(10,80,29),
            array(0,55,36),
            array(4,62,37),
            array(6,68,32),
            array(13,80,35),
            array(0,53,40),
            array(5,62,41),
            array(7,68,42),
            array(15,80,44)
        ),"age","ef");
    }

    public function combinedValues() {
        return $this->buildScoreProvider(
            array(
                array(13,array(
                    MAGGICRiskFactors::MALE=>true,
                    MAGGICRiskFactors::CURRENT_SMOKER=>true,
                    MAGGICRiskFactors::DIABETIC=>true,
                    MAGGICRiskFactors::COPD_DIAGNOSIS=>true,
                    MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>true,
                    MAGGICRiskFactors::NOT_BETA_BLOCKER=>true,
                    MAGGICRiskFactors::NOT_ACEI_ARB=>true
                )),
                array(0,array(
                    MAGGICRiskFactors::MALE=>false,
                    MAGGICRiskFactors::CURRENT_SMOKER=>false,
                    MAGGICRiskFactors::DIABETIC=>false,
                    MAGGICRiskFactors::COPD_DIAGNOSIS=>false,
                    MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>false,
                    MAGGICRiskFactors::NOT_BETA_BLOCKER=>false,
                    MAGGICRiskFactors::NOT_ACEI_ARB=>false
                )),
                array(6,array(
                    MAGGICRiskFactors::MALE=>true,
                    MAGGICRiskFactors::CURRENT_SMOKER=>false,
                    MAGGICRiskFactors::DIABETIC=>false,
                    MAGGICRiskFactors::COPD_DIAGNOSIS=>true,
                    MAGGICRiskFactors::FIRST_DIAG_HF_PAST_18_MONTHS=>false,
                    MAGGICRiskFactors::NOT_BETA_BLOCKER=>true,
                    MAGGICRiskFactors::NOT_ACEI_ARB=>false
                ))
        ),"combined_values");
    }
}