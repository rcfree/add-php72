<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HoursSinceAdmission
 *
 * @author rob
 */
class ADD_Component_LengthOfStay extends ADD_Component implements ADD_Component_AjaxInterface {
    function ajaxFunction($values, ADD_Page $callPage) {
        $options = $callPage->getParentPage()->getComponent("TimeSinceSpell")->getAllSettings();
        $endPointDate = \ClinicLE\Common\Util::val("outcome_date",$values);
        ADD::sys()->setParam("eid",\ClinicLE\Common\Util::val("eid",$values));

        $section = $callPage->getParentPage()->getEncounterRecord(\ClinicLE\Common\Util::val("eid",$values));
        $section->retrieve();
        $episodeId = $section->getEncData("episode_id");

        $timespan = new \ClinicLE\Episode\Util\EpisodeTimespan(new \ClinicLE\Common\Config\ArrayConfig
        ($options),$callPage
            ->getModule(),
            $section,$episodeId);

        $timespan->setEndPointDate(DateTime::createFromFormat("d/m/Y",$endPointDate));

        try {
            $tsValue = $timespan->determineTimespan();
        }
        catch(\ClinicLE\Common\Exception\NullValueException $ex) {
            return array("length_of_hospital_stay"=>null);
        }

		return array("length_of_hospital_stay"=>$tsValue);
	}

    function ajaxInputs(ADD_Page $callPage)
    {
        return array("outcome_date");
    }
}
