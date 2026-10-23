<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/08/16
 * Time: 16:38
 */
namespace ClinicLE\Episode\Util;

use ClinicLE\Common\Config\AbstractConfig;
use ADD;
use ClinicLE\Common\Exception\NullValueException;
use DateTime;

class EpisodeTimespan {
    private $options;
    private $module;
    private $section;
    private $episodeId;
    private $startPointDate;
    private $endPointDate;
    private $noEndPoint=false;

    /**
     * @return boolean
     */
    public function isNoEndPoint()
    {
        return $this->noEndPoint;
    }

    /**
     * @param mixed $startPointDate
     */
    public function setStartPointDate($startPointDate)
    {
        $this->startPointDate = $startPointDate;
    }

    /**
     * @param mixed $endPointDate
     */
    public function setEndPointDate($endPointDate)
    {
        $this->endPointDate = $endPointDate;
    }

    public function __construct(AbstractConfig $options, $module, $section, $episodeId) {
        $this->options = $options;
        $this->module = $module;
        $this->section = $section;
        $this->episodeId = $episodeId;
    }
    public function determineTimespan() {
        $startPointDt = $this->getStartPointDate();
        if(!$startPointDt) {
            return "";
        }

        $endPointDt = $this->getEndPointDate();

        if(!$endPointDt && $this->options->has("now_if_no_end")) {
            $endPointDt = new DateTime();
            $this->noEndPoint=true;
        }

        if(!$endPointDt) {
            throw new NullValueException("No end point value");
        }

        $diffDi = $startPointDt->diff($endPointDt);
        $days = $diffDi->format("%a");

        if($this->options->getSetting("scale")=="hours") {
            $hours = $diffDi->format("%h");
            $hours += $days * 24;
            $timespan = "$hours";
        }
        else {
            $timespan = "$days";
        }

        return $timespan;
    }
    
    private function getEndPointDate() {
        if($this->endPointDate) {
            return $this->endPointDate;
        }
        $epPage = $this->module->getPage($this->options->assertSetting("end_point_page"));

        $epRecord = ADD::sys()->getEncounterManager()->child($this->section,$epPage->getSetting("encounter"));
        $epRecord->retrieve();
        if($this->options->getSetting("end_point_field")) {
            $endPointAsDate = $this->options->getSetting('end_point_as_date');

            $dateCreated = $epRecord->getObsValue($this->options->getSetting("end_point_field"));
            $createdDt = DateTime::createFromFormat($endPointAsDate ? "d/m/Y" : "d/m/Y H:i", $dateCreated);
        }
        else {
            $dateCreated = $epRecord->getEncData("date_created");
            $createdDt = DateTime::createFromFormat("Y-m-d H:i:s", $dateCreated);
        }
        return $createdDt;
    }

    private function getStartPointDate() {
        if($this->startPointDate) {
            return $this->startPointDate;
        }
        $episodeId = $this->episodeId;
        $db = ADD::db('BASE');
        $spPage = $this->module->getPage($this->options->getSetting("start_point_page","ascreening"));
        $spParent = $spPage->getParent();

        $sectionId = $db->fetchSingleValue("select s.id from section s left join section_type st on s.section_type_id=st.id where st.name=? and s.episode_id=?",array($spParent->getSetting("encounter"),$episodeId));
        if(!$sectionId) {
            return null;
        }

        $em = ADD::sys()->getEncounterManager();
        if(!$sectionId) {
            return null;
        }
        $section = $em->sectionById($sectionId);
        $section->retrieve();
        $detail = $em->child($section,$spPage->getSetting("encounter"));
        $detail->retrieve();

        $startDt = DateTime::createFromFormat("d/m/Y H:i",$detail->getObsValue($this->options->getSetting("start_point_field","admission_datetime")));
        if(!$startDt) {
            return null;
        }
        return $startDt;
    }
}
