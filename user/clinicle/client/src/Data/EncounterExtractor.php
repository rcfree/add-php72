<?php
namespace ClinicLE\Client\Data;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\Data\Query\QueryRunner;
use CLE;
use ADD_Database_Encounter as EncounterDatabase;
use ClinicLE\Common\Util;
use ClinicLE\DataFrame\DataFrame;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 12:19
 */
class EncounterExtractor extends AbstractEncounterExtractor
{
    private $encounterPage;
    private $encounterForm;
    private $includeAllFields = true;

    /**
     * @return bool
     */
    public function areAllFieldsIncluded()
    {
        return $this->includeAllFields;
    }

    /**
     * @param bool $includeAllFields
     */
    public function setIncludeAllFields($includeAllFields)
    {
        $this->includeAllFields = $includeAllFields;
    }


    function finaliseQuery(EncounterExtractorParams $params) {
        $sortBy = $params->getSortField();
        $cdt = $this->getFieldSubQuery($sortBy);

        if(!$sortBy) {
            return;
        }
        if($params->isSortedAsDate()===true) {
            $this->getQuery()->orderBy("STR_TO_DATE($cdt,'%d/%m/%Y') ",$params->getSortOrder());
        }
        else {
            $this->getQuery()->orderBy($cdt,$params->getSortOrder());
        }
    }

function getFieldSubQuery($fieldName) {

    return "(select concat(o.value, coalesce(o.note,'')) from observation o
					left join observation_field of on o.observation_field_id=of.id
					where o.encounter_id=e.id and of.name='$fieldName'
				)";
}

function appendFiltersToQuery(EncounterExtractorParams $params) {
    if(!$params->getFilters()) return;
    $query = $this->getQuery();

    foreach($$params->getFilters() as $filter) {
        $fieldSquery = $this->getFieldSubQuery($filter->getField());
        if($filter->getValues()==array("null")) {
            $query->addFilter("(".$fieldSquery." is null or $fieldSquery = '')");
        }
        else {
            $query->filterByObservationField($filter->getField(),$filter->getValues());
        }
    }

}
function determineFieldOutput($obsName,$obsValue) {
    $field = $this->encounterForm->getField($obsName);
    if(!$field) {
        CLE::log()->warn("field $obsName not found");
        return $obsValue;
    }
    $field->setValue($obsValue);

    return array("value"=>$obsValue,"label"=>$field->getDisplayText($this->encounterPage,null));
}

    /**
     * @return mixed
     */
    public function getEncounterPage()
    {
        return $this->encounterPage;
    }

    /**
     * @return mixed
     */
    public function getEncounterForm()
    {
        return $this->encounterForm;
    }

    /**
     * @param EncounterExtractorParams $params
     */
    public function populateQuery(EncounterExtractorParams $params)
    {
        $add = CLE::sys();

        $query = $this->getQuery();

        $specialStatus = $params->getSpecialStatus();
        $confirmedParentType = $params->getConfirmedParentType();

        $temp = explode(":", $params->getPim());
        $pageInModule = $add->getPageInModule($temp[0], $temp[1]);
        $this->encounterPage = $pageInModule;

        if (!$pageInModule->getPermission("view")) throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to view " . $pageInModule->getSetting('single') . "s");


        $badge = $pageInModule->getSetting('badge');

        $encType = $pageInModule->getSetting('encounter');

        $this->encounterForm = $pageInModule->getComponent('Form');
        $this->encounterForm->setup($pageInModule);

        $query->filterByType($encType);

        /*if($this->areAllFieldsIncluded()===true) {
            $query->addEncounterFields("*");
            $query->addObservationFieldsFromForm($this->encounterForm);
        }*/

        if ($params->getPatientId()) {
            $query->filterByPatientId($params->getPatientId());
        }

        if ($params->getParentId()) {
            $query->filterBySectionId($params->getParentId());
        }

        if ($confirmedParentType) {
            $parentPage = $add->getPageFromPim($confirmedParentType);
            if ($parentPage && $parentPage->getSetting('encounter')) {
                $query->filterBySectionType($parentPage->getSetting('encounter'));
            }
        }

        if ($specialStatus) {
            $query->filterByPatientField("special_status", $specialStatus);
        } else {
            $query->filterByPatientField("special_status", null, "is");
        }
        if ($badge) {
            $query->filterByEncounterField("badge", $badge);
        }

        $this->appendFiltersToQuery($params);

        $pageInModule->setupComponents();
        $form = $pageInModule->getComponent('Form');
        foreach ($params->getFilters() as $filter) {
            $fieldName = $filter->getField();
            $formField = $form->getField($fieldName);
            $formField->finaliseField($pageInModule);

            if (!is_a($formField, "ADD_Field_List")) continue;
            $optionInfo = array();
            foreach ($filter->getValues() as $value) {
                $optionInfo[] = $value . "=" . $formField->getOptions()->findLabelWithValue($value);
            }
            CLE::web()->addWarning("Filtering '" . $fieldName . "' on <ul><li>" . implode("</li><li>", $optionInfo) . "</li></ul>");
        }
        $this->finaliseQuery($params);
    }

}