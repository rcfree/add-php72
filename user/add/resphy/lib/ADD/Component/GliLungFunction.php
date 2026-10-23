<?php
use ADD\Resphy\GliLungFunctionCalculator;

class ADD_Component_GliLungFunction extends ADD_Component_LungFunction {
	function ajaxInputs(\ADD_Page $callPage) {
            return array('spirometry_date','height', 'cfactor_90pc');
	}

    function beforeSubmit(&$values)
    {
        $comments = \ClinicLE\Common\Util::val("lf_comments",$values);
        if (strlen($comments)>0) {
            $comments = $comments . "\n";
        }
        else {
            $comments = "";
        }
        $values['lf_comments'] = $comments. "GLI ethnicity value used:".$values['cfactor_90pc'];
        $values['cfactor_90pc']=0;
    }

    function ajaxFunction($values, ADD_Page $callPage) {
        $lfValues = self::calcLungFunctionValues($values, $callPage);
        $settings = $callPage -> getSettingsFromArray(ADD_Component_LungFunction::fieldSettings(), true);
        $lfFields = array();
        foreach ($settings as $name => $fieldName) {
            $value = $lfValues[$name];
            $rounded = $value ? round($value, 2) : "";
            $lfFields[$fieldName] = $rounded;
        }
        return $lfFields;
    }

    static function calcLungFunctionValues($values, $page) {
        $add = ADD::sys();
        $pid = $add -> getPatientId();
        $db = ADD::db('encounter');
        $pdb = ADD::db('patient');
        $heightSource = "details";

        $reqdSettings = array('LungFunction.height_page', 'LungFunction.height_field', 'LungFunction.date_page', 'LungFunction.date_field','LungFunction.eth_page','LungFunction.eth_field');
        list($heightPageName, $heightField, $datePageName, $dateField, $ethPageName, $ethField) = $page -> getSettingsFromArray($reqdSettings, false, true);
        list($height) = ADD_Field_Util::getSiblingObs($page, $heightPageName, array($heightField), "calculate GLI predicted lung function values", $values);
        list($firstVisit) = ADD_Field_Util::getSiblingObs($page, $datePageName, array($dateField), "calculate GLI predicted lung function values", $values);
        list($eth) = ADD_Field_Util::getSiblingObs($page, $ethPageName, array($ethField), "calculate GLI predicted lung function values", $values);

        $patient = $pdb -> getPatient($pid);
        $dob = $patient['date_of_birth'];
        $pred =  GliLungFunctionCalculator::PREFIX;

        $nullValues = array("{$pred}_fev1" => null, "{$pred}_fvc" => null, "{$pred}_fev1_fvc" => null, "{$pred}_pef" => null);

        if ($firstVisit == "__/__/____")
            $firstVisit = null;

        if (!$firstVisit)
            return $nullValues;
        if (!$height)
            return $nullValues;

        $age = ADD_Field_Util::age($dob, $firstVisit);

        $gender = $patient['gender'];

        if ($gender!="M" && $gender!="F") {
            $page -> addWarning("Gender is not Male or Female. Unable to calculate predicted lung function values.");
            return $nullValues;
        }

        if (!$age) {
            $page -> addWarning("Age could not be calculated. There may be a problem with the date of birth value.");
            return $nullValues;
        }
        $resphyConfig = \CLE::sys()->getConfig()->getSection("Resphy");
        if(!$resphyConfig) {
            throw new NullValueException("No 'Resphy' section in config");
        }
        $calculator = new GliLungFunctionCalculator($gender, $height, $age, $eth, $resphyConfig->getSetting("rspiro_path"));

        try {
            $lfValues = $calculator->calculatePredValues();
        }
        catch(\Kachkaev\PHPR\Exception\RErrorsException $ex) {
            throw new ADD_AjaxFieldException("Unable to calculate values. ".print_r($ex->getErrors()[0]->getErrorMessage(),1),"","");
        }

        return $lfValues;
    }


}
?>