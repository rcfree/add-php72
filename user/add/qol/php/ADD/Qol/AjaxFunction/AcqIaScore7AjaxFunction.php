<?php
namespace ADD\Qol\AjaxFunction;
use ClinicLE\Ajax\AbstractAjaxFunction;
use Symfony\Component\HttpFoundation\Request;

class AcqIaScore7AjaxFunction extends AbstractAjaxFunction {
	function runFunction(Request $request)
    {
        $pre_bronch = (float)$request->query->get("fev1_pre_bronch");
        $pred = (float)$request->query->get("fev1_predicted");

        if (!$pre_bronch || !$pred) {
            return array("score_7"=>"","fev1_pc_predicted"=>"");
        }
        $fev1_pc_pred = ($pre_bronch/$pred) * 100;

        $score7 = "";

        if ($fev1_pc_pred>95) {
            $score7 = "0";
        }
        if ($fev1_pc_pred>=90 && $fev1_pc_pred<=95) {
            $score7 = "1";
        }
        if ($fev1_pc_pred>=80 && $fev1_pc_pred<=89) {
            $score7 = "2";
        }
        if ($fev1_pc_pred>=70 && $fev1_pc_pred<=79) {
            $score7 = "3";
        }
        if ($fev1_pc_pred>=60 && $fev1_pc_pred<=69) {
            $score7 = "4";
        }
        if ($fev1_pc_pred>=50 && $fev1_pc_pred<=59) {
            $score7 = "5";
        }
        if ($fev1_pc_pred<50) {
            $score7 = "6";
        }

        return array("score_7"=>array("value"=>$score7), "fev1_pc_predicted"=>array("value"=>number_format(round($fev1_pc_pred,1),1)));
    }

    function getInputFields()
    {
        return array("fev1_pre_bronch","fev1_predicted");
    }
}
