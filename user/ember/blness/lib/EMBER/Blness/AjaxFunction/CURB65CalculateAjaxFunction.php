<?php
namespace EMBER\Blness\AjaxFunction;
use ClinicLE\Ajax\AbstractAjaxFunction;
use Clinicle\Search\LinkedRecordQuery;
use EMBER\Calculate\CURB65Score;
use EMBER\Calculate\DECAFScore;
use Symfony\Component\HttpFoundation\Request;
use CLE;

class CURB65CalculateAjaxFunction extends AbstractAjaxFunction {
	function runFunction(Request $request)
    {
        $add = CLE::sys();
        $pid = $add -> getPatientId();
        $callPage = $this->getCallingPage();

        
        $values = $request->request->all();

        list($urea) = \ADD_Field_Util::getSiblingObs($callPage, "ablood", array(
            "biochem_value_urea"),
            "calculate CURB65 score", $values);
        list($dov,$confusion,$sbp,$dbp,$respRate) = \ADD_Field_Util::getSiblingObs($callPage, "ademog", array
        ("enrolled_date","cognitive_impairment_on_admiss","bp_top","bp_bottom","resp_rate"),
        "calculate CURB65 
		score", $values);

        $patient = \CLE::db('patient') -> getPatient($pid);
        $dob = $patient['date_of_birth'];

        $curb65 = new CURB65Score();

        $score = $curb65->determineScore($confusion,$urea,$respRate,$sbp,$dbp,$dov,$dob);
        return array("curb65_score"=>$score);
    }

    function getInputFields()
    {
        return array();
    }
}
