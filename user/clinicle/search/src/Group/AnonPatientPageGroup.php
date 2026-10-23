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
use ClinicLE\System\Factory\SystemFactories;

class AnonPatientPageGroup extends AbstractPatientPageGroup
{
    function __construct(ModuleCollection $modules) {
        $patientPage = $modules->get("patient")->getPage("demographics");
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        $dobField = $rowFieldFactory->newInstance(array("Field"=>"date_of_birth","Title"=>"Date of birth","Type"=>"Date","Settings"=>array("datepicker"=>true)));

        $this->addSearchType(new AgeSearchType($dobField,"Date of birth"));
        parent::__construct($patientPage);
    }
    function getTitle() {
        return "Anonymous patient demographics";
    }
    function getFieldNames()
    {
        return array("gender","ethnic_group","marital_status","born_in_uk","country_of_birth","deceased");
    }
    function getName() {
        return "anon_patient";
    }

}