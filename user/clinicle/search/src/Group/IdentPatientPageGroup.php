<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:56
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Module\ModuleCollection;
use ClinicLE\Search\SearchType\AgeSearchType;
use ClinicLE\Search\SearchType\DateSearchType;
use ClinicLE\Search\SearchType\LinkedTextSearchType;
use ClinicLE\System\Factory\SystemFactories;

class IdentPatientPageGroup extends AbstractPatientPageGroup
{
    function __construct(ModuleCollection $modules) {
        $patientPage = $modules->get("patient")->getPage("demographics");
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        $dobField = $rowFieldFactory->newInstance(array("Field"=>"date_of_birth","Title"=>"Date of birth","Type"=>"Date","Settings"=>array("datepicker"=>true)));
        $identField = $rowFieldFactory->newInstance(array("Field"=>"id","Title"=>"Patient link","Type"=>"text"));

        $this->addSearchType(new AgeSearchType($dobField,"Date of birth"));
        $this->addSearchType(new LinkedTextSearchType($identField, "Patient link"));
        parent::__construct($patientPage);
    }
    function getTitle() {
        return "Identifiable patient demographics";
    }
    function getFieldNames()
    {
        return array("identifier", "first_name","last_name","gender","ethnic_group","nhs_no","marital_status","hospital_no","born_in_uk","country_of_birth","deceased");
    }
    function extendSearchTypes() {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        $dobField = $rowFieldFactory->newInstance(array("Field"=>"date_of_birth","Title"=>"Date of birth","Type"=>"Date","Settings"=>array("datepicker"=>true)));
        $dodField = $rowFieldFactory->newInstance(array("Field"=>"date_of_death","Title"=>"Date of death","Type"=>"Date","Settings"=>array("datepicker"=>true)));

        $this->addSearchType(new DateSearchType($dobField,"Date of birth",true));
        $this->addSearchType(new DateSearchType($dodField,"Date of death",true));
    }
    function getName()
    {
        return "ident_patient";
    }
}