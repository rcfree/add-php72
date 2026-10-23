<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 14:16
 */

namespace EMBER\Blness\Data;


use ADD_Database_Encounter as EncounterDatabase;
use ClinicLE\Client\Data\AbstractEncounterExtractor;
use ClinicLE\Client\Data\EncounterExtractor;
use ClinicLE\Client\Data\EncounterExtractorParams;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\Data\Query\QueryRunner;
use ClinicLE\DataFrame\DataFrame;

class EmberPatientExtractor extends AbstractEncounterExtractor
{

    function populateQuery(EncounterExtractorParams $params)
    {
        $pageMap = $params->extractPageMap();
        $rootType = $params->getRootType();
        $diagnosisType = $params->getDiagnosisType();
        $visitSection = $params->getVisitSection();
        $demogType = "blness-admission-demog";
        $screeningType = "blness-admission-screening";
        $ewdType = "blness-eoswidis-details";

        $query = $this->getQuery(); /** @var AbstractEncounterQuery */
        $query->filterByType($rootType);

        $sectType = $this->getEncounterDb()->getSectionTypeWithName($visitSection);

        $query->joinSection($visitSection,$sectType['id']);

        $query->addJoinTable("episode `ep`","`$visitSection`.episode_id = ep.id");

        $query->addEncounterField("id");
        $query->addEncounterField("patient_id");
        $query->addEncounterField("parent_encounter_id");
        $query->addEncounterField("episode_id","episode_id,",$visitSection);
        $query->filterByEncounterField("is_deleted",1,"!=","ep");

        $demogForm = $this->getEncounterDb()->getEncounterFormWithType($demogType);
        $screeningForm = $this->getEncounterDb()->getEncounterFormWithType($screeningType);
        $diagnosisForm = $this->getEncounterDb()->getEncounterFormWithType($diagnosisType);


        $query->joinSectionEncounter($diagnosisType, $diagnosisForm['id']);

        if($visitSection!="blness-admission") {
            $query->addJoinTable("section `blness-admission`","`blness-admission`.episode_id = `ep`.id");
            $query->addJoinTable("encounter `$demogType`","`blness-admission`.id = `$demogType`.parent_encounter_id and `$demogType`.encounter_form_id=".$demogForm['id'],"inner");
            $query->addJoinTable("encounter `$screeningType`","`blness-admission`.id = `$screeningType`.parent_encounter_id and `$screeningType`.encounter_form_id=".$screeningForm['id'],"inner");
        }
        else {
            $query->joinSectionEncounter($demogType, $demogForm['id']);
        }

        $query->addObservationField("study_number","Number",$demogType);
        $query->addObservationField("primary_condition","Diagnosis",$diagnosisType);


        $query->addObservationField("treat_as","Type",$screeningType);
        $query->addObservationField("enrolled_datetime","Enrolled",$demogType);
        foreach ($pageMap as
                 $apageName => $apage) {
            $encType = $apage->getSetting("encounter");
            if($encType!==$diagnosisType && $encType!=$demogType) {
                $encForm = $this->getEncounterDb()->getEncounterFormWithType($encType);
                $query->joinSectionEncounter($encType, $encForm['id']);
            }
            $query->addEncounterField("is_complete", $apage->getSetting("EmberPatients.label"), $encType);
        }

        $emberSearch = $params->getEmberSearch();
        if ($emberSearch) {
            $query->filterByObservationField("study_number", $emberSearch,"like",$demogType);
        }

        if($params->getSortField()) {
            $query->orderBy($params->getSortExpression(), $params->getSortOrder());
        }
    }
    function determineFieldOutput($obsName,$obsValue) {
        if($obsName=="Diagnosis") {
            $obsValue = Util::unserialize($obsValue);
            if(is_array($obsValue)) {
                $obsValue = implode(", ", array_values($obsValue));
            }
        }
        return array("value"=>$obsValue,"label"=>$obsValue);
    }

}