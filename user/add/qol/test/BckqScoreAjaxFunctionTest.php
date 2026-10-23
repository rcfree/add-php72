<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 03/01/17
 * Time: 14:06
 */

namespace ADD\Qol\Test;


use ADD\Qol\AjaxFunction\BckqScoreAjaxFunction;
use ClinicLE\Testing\AbstractBaseTest;
use Symfony\Component\HttpFoundation\Request;
use CLE;

class BckqScoreAjaxFunctionTest extends AbstractBaseTest 
{
    private $allCorrect = array(
        "chronic_means_severe"=>2,
        "confirm_by_breath_tests"=>1,
        "gradual_worsening"=>1,
        "blood_o2_levels_always_low"=>2,
        "unusual_in_ppl_lt_40_yo"=>1,
        "gt_80_pc_cig_smoking"=>1,
        "occup_dust_exp_cause"=>1,
        "asthma_devel_copd"=>1,
        "copd_inherited"=>2,
        "women_less_vulner"=>2,
        "swelling_ankles"=>2,
        "fatigue"=>1,
        "wheezing"=>1,
        "chest_pain"=>2,
        "weight_loss"=>2,
        "bless_prevents_air_travel"=>2,
        "bless_worse_large_meals"=>1,
        "bless_o2_low"=>2,
        "bless_exercise_norm_resp"=>1,
        "bless_bronch_tube_narrow"=>1,
        "phlegm_common_symptom"=>1,
        "clear_phlegm_dehyd_diff"=>1,
        "bdilator_help_clear_phlegm"=>1,
        "phlegm_swallow_harm"=>2,
        "breath_exer_assist_clearing"=>1,
        "infect_cough_blood"=>2,
        "infect_phlegm_colour"=>1,
        "exacerb_infection_absence"=>1,
        "high_tem_chest_infect"=>2,
        "exacerb_steroid_tab"=>2,
        "walking_bt_breath_exer"=>1,
        "exerc_strains_lungs"=>2,
        "exerc_mains_bone_dens"=>1,
        "exerc_relieve_depression"=>1,
        "exerc_stop_breathless"=>2,
        "stop_smoking_heart_disease"=>1,
        "stop_smoking_slows_damage"=>1,
        "stop_smoking_pointless"=>2,
        "stop_smoking_improves_lung"=>2,
        "prescrip_nic_replace_therapy"=>2,
        "flu_jab_every_year"=>1,
        "flu_jab_gives_flu"=>2,
        "flu_jab_ge_65_yo"=>2,
        "pneum_protects_all"=>2,
        "pneum_flu_jab_same_day"=>1,
        "bdil_act_quickly"=>2,
        "short_long_bdil_same_day"=>1,
        "spacers_dried_after_washing"=>2,
        "spacer_increase_drug_in_lungs"=>1,
        "tremor_bdil_side_effect"=>1,
        "ab_course_ge_10_days"=>2,
        "excess_ab_resistance"=>1,
        "ab_clear_all_infect"=>2,
        "ab_necess_exacerb"=>2,
        "seek_advice_if_diarr"=>1,
        "steroid_strength_muscles"=>2,
        "steroid_avoid_infect"=>2,
        "side_effects_short_course_less"=>1,
        "indigest_steroid_side_effect"=>1,
        "steroid_inc_appetite"=>1,
        "inh_steroids_stop_if_tablets"=>2,
        "inh_steroids_bless_relief"=>2,
        "spacer_reduce_thrush"=>1,
        "inh_steroid_before_bdilator"=>2,
        "inh_steroid_improve_lung"=>2,
    );
    function setup() {
        $sys = $this->initMockSystem();
        $sys->initServiceContainer();
        $this->callingPage = $this->getMockBuilder('\ADD_Page')->disableOriginalConstructor()->getMock();
        $formFile = "qol-bckq.xlsx";

        $this->callingPage->expects($this->any())->method("getFilePath")->will($this->returnValue
        (__DIR__."/../"));
        $this->callingPage->expects($this->any())->method("getSetting")->with("form")->will($this->returnValue
        ($formFile));
        $callingForm = new \ADD_Component_Form_File($this->callingPage);
        $this->callingPage->expects($this->any())->method("getComponent")->with("Form")->will($this->returnValue
        ($callingForm));
        $this->SUT = new BckqScoreAjaxFunction($this->callingPage);
    }

    /**
     * @test
     */
    function empty_answers_calculates_blank_score() {
        $request = new Request(array());
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score"=>""),$results);
    }

    /**
     * @test
     */
    function incomplete_answers_calculate_blank_score() {
        $request = new Request(array("chronic_means_severe"=>2));
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score"=>""),$results);
    }

    /**
     * @test
     */
    function all_correct_answers_calculates_maximum_score() {
        $request = new Request($this->allCorrect);
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score"=>65),$results);
    }

    /**
     * @test
     */
    function some_correct_answers_calculates_expected_score() {
        $someCorrect = $this->allCorrect;
        $someCorrect = array_merge($someCorrect,array("inh_steroids_stop_if_tablets"=>1,
        "inh_steroids_bless_relief"=>1,
        "spacer_reduce_thrush"=>2,
        "inh_steroid_before_bdilator"=>1,
        "inh_steroid_improve_lung"=>1));

        $request = new Request($someCorrect);
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(array("score"=>60),$results);
    }

    /**
     * @test
     */
    function scores_only_flag_returns_null_score() {
        $query = $this->allCorrect;
        $query['scores_only']=1;
        $request = new Request($query);
        $results = $this->SUT->runFunction($request);
        $this->assertEquals(null,$results);
    }

    /**
     * @test
     */
    function getInputFields_returns_only_question_fields() {
        $fields = $this->SUT->getInputFields();
        $this->assertEquals($fields,array_keys($this->allCorrect));
    }
}