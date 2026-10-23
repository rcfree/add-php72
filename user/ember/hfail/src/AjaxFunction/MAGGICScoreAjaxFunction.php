<?php
namespace EMBER\HFail\AjaxFunction;

use ClinicLE\Data\PageFieldSearch\PageFieldSearchCreator;
use ClinicLE\RiskScore\AbstractRiskScoreAjaxFunction;

use EMBER\HFail\MAGGIC\MAGGICRiskScore;
use EMBER\HFail\MAGGIC\MAGGICRiskScoreParamsGenerator;
use Symfony\Component\HttpFoundation\Request;
use CLE;

class MAGGICScoreAjaxFunction extends AbstractRiskScoreAjaxFunction {
    function getScoreMessage() {
        return "calculate MAGGIC score";
    }
    function createParamsGenerator(PageFieldSearchCreator $creator) {
        return new MAGGICRiskScoreParamsGenerator($creator);
    }
    function getOutputField() {
        return "maggic_score";
    }
    function createScoreClass() {
        return new MAGGICRiskScore();
    }

    function getInputFields()
    {
        return array("nyha_class","diabetes","copd","first_diag_hf_lt18m","on_beta_blocker","on_acei_arb");
    }
}
