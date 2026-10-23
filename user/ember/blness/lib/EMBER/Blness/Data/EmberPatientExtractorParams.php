<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 14:16
 */

namespace EMBER\Blness\Data;

use ClinicLE\Client\Data\EncounterExtractorParams;
use ClinicLE\Module\Module;

class EmberPatientExtractorParams extends EncounterExtractorParams
{
    private $emberSearch;
    private $emberModule;
    private $emberVisit="Visit 1A";
    private $visitSection;
    /**
     * @return mixed
     */
    public function getEmberVisit()
    {
        return $this->emberVisit;
    }

    /**
     * @param mixed $emberVisit
     */
    public function setEmberVisit($emberVisit)
    {
        $this->emberVisit = $emberVisit;
    }

    function __construct(Module $emberModule, $emberVisit) {
        parent::__construct("blness:ademog");
        $this->emberModule = $emberModule;
        $this->emberVisit = $emberVisit;
    }

    /**
     * @return mixed
     */
    public function getEmberSearch()
    {
        return $this->emberSearch;
    }

    /**3
     * @param mixed $emberSearch
     */
    public function setEmberSearch($emberSearch)
    {
        $this->emberSearch = $emberSearch;
    }

    private function findVisitSectionPage() {
        foreach($this->emberModule->getPages() as $page) {
            $parentSectionLabel = $page->getSetting("EmberPatients.section_label");
            if($parentSectionLabel==$this->emberVisit) {
                return $page;
            }
        }
    }
    function extractPageMap() {
        $pageMap = array();
        $visitPage = $this->findVisitSectionPage();
        $this->visitSection = $visitPage->getSetting("encounter");
        $pages = $visitPage->getChildPages();

        usort($pages,function($a,$b) {
            $aWeight = intval($a->getSetting("weight",999));
            $bWeight = intval($b->getSetting("weight",999));
            if($aWeight==$bWeight) {
                return 0;
            }
            return ($aWeight>$bWeight) ? +1 : -1;
        });
        foreach($pages as $page) {
            $label = $page->getSetting("EmberPatients.label");

            if(!$label) {
                continue;
            }

            if(!$page->hasAnyPermissions(array("create","view","edit"))) {
                continue;
            }

            $pageMap[$page->getName()]=$page;
        }
        return $pageMap;
    }

    function getDiagnosisType() {
        return $this->visitSection."-diagnosis";
    }
    function getVisitSection() {
        return $this->visitSection;
    }
    function getRootType() {
        if($this->visitSection == "blness-admission") {
            return "blness-admission-screening";
        }
        if($this->visitSection == "blness-discharge") {
            return "blness-discharge-outcome";
        }
        if($this->visitSection == "blness-recovery") {
            return "blness-recovery-demog";
        }
    }
    function getSortExpression() {
        if($this->getSortField()=="study_number") {
            return "(select o.value from observation o left join observation_field of on o.observation_field_id=of.id where o.encounter_id=`blness-admission-demog`.id and of.name='study_number')";
        }
        if($this->getSortField()=="enrolled_datetime") {
            return "(select STR_TO_DATE(o.value,'%d/%m/%Y %H:%i') from observation o left join observation_field of on o.observation_field_id=of.id where o.encounter_id=`blness-admission-demog`.id and of.name='enrolled_datetime')";
        }
    }

}
