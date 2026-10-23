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
class ADD_Component_TimeSinceSpell extends ADD_Component {
	public function init() {
        $events = $this->getPage()->getEvents();
        $events->addListener("episodeSection.menuSectionLabel",array($this,"onMenuSectionLabel"));
	}

    public function onMenuSectionLabel($event) {
        $timespan = new \ClinicLE\Episode\Util\EpisodeTimespan(new \ClinicLE\Common\Config\ArrayConfig
        ($this->getAllSettings()),$this->getPage()
            ->getModule(),
            $event->getSection(),$event->getEpisodeId());

        $label = $event->getLabel();

        try {
            $tsValue = $timespan->determineTimespan();

            if(ADD_Util::isEmpty($tsValue)) {
                return;
            }
            if($timespan->isNoEndPoint() && $this->getSetting("now_if_no_end")) {
                $noEndPointLabel = $this->getSetting("no_end_point_text");
                $label.="<span class='label label-warning'>".sprintf($noEndPointLabel,$tsValue)
                    ."</span>";
            }
            else {
                $startPointLabel = $this->getSetting("start_point_text","start");
                $label .= "<br/>" . sprintf($startPointLabel, $tsValue);
            }
        }
        catch(\ClinicLE\Common\Exception\NullValueException $ex) {

            $label.="<span class='label label-warning'>".$this->getSetting("no_end_point_text","No end point")
            ."</span>";
            $event->setLabel($label);
            return;
        }
		
		$event->setLabel($label);		
	}

}
