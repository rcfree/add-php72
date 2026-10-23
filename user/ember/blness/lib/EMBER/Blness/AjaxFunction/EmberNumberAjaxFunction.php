<?php
namespace EMBER\Blness\AjaxFunction;
use ClinicLE\Ajax\AbstractAjaxFunction;
use ClinicLE\Common\Util;
use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 19/04/17
 * Time: 10:24
 */
class EmberNumberAjaxFunction extends AbstractAjaxFunction
{

    function runFunction(Request $request)
    {
        $studyNumber = $request->query->get("study_number");
        if(Util::isEmpty($studyNumber)) {
            return;
        }

        $ucaseStudyNumber = strtoupper($studyNumber);

        if(!$this->isCorrectFormat($ucaseStudyNumber)) {
            throw new \ADD_AjaxFieldException(" $studyNumber is not valid", "EMBER study number", "study_number");
        }
        if(!$this->isUnique($ucaseStudyNumber,$request)) {
            throw new \ADD_AjaxFieldException(" $studyNumber has been used for a different patient", "EMBER study number", "study_number");
        }
        if($studyNumber != $ucaseStudyNumber) {
            return array("study_number"=>array("value"=>$ucaseStudyNumber));
        }
        return;
    }

    function isUnique($studyNumber, Request $request) {
        $edb = \CLE::db("encounter");
        $encs = $edb->getEncountersWithObservationValue("blness-admission-demog", "study_number",$studyNumber);
        if(count($encs)==0) {
            return true;
        }

        $pid = \CLE::sys()->getPatientId();

        foreach($encs as $enc) {
            if($enc['is_deleted']==1) {
                return true;
            }
            if($enc['patient_id']!=$pid) {
                return false;
            }

        }

        return true;
    }

    function isCorrectFormat($studyNumber)
    {
        $regex = \CLE::sys()->getModule("ember/blness")->getTemplate()->assertSetting("allowed_prefix_regex");
        if (!preg_match($regex, $studyNumber)) {
            return false;
        }
        return true;
    }

    function getInputFields()
    {
        return array("study_number");
    }
}