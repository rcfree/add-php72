<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/01/17
 * Time: 11:29
 */

namespace ClinicLE\Search\Query;


use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Common\Util;
use ClinicLE\Search\Group\AbstractPatientPageGroup;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Util\ParamSelectStatement;

class DataQueryRunnerFactory
{
    function __construct(GroupSet $groupSet) {
        $this->groupSet = $groupSet;
    }

    /**
     * @param $searchData
     * @return QueryRunner
     */
    function createRunner($searchData=array()) {
        $runner = new QueryRunner($this->groupSet,\CLE::sys()->getEvents());
        $options = Util::val("options",$searchData,array());
        $runner->setSearchOptions(new ArrayConfig($options));
        $patientGroupIncluded = false;

        foreach($searchData["groups"] as $groupData) {
            $settings = $groupData['settings'] + $options;
            $groupQuery = $runner->newGroupQuery($groupData['name'], $settings);
            if(is_a($groupQuery->getGroup(), AbstractPatientPageGroup::class)) {
                $patientGroupIncluded=true;
            }
            $filters = $groupData['filters'];
            foreach($filters as $filter) {
                $groupQuery->addFilter($filter['field'],$filter['operation'],$filter['values']);
            }

            $attributes = $groupData['attributes'];
            $groupQuery->addAttributes($attributes);
            $settings = $groupData['settings'];
            $groupQuery->setSettings(new ArrayConfig($settings));
        }
        /*if($patientGroupIncluded===false) {
            $patientGroupQuery = $runner->newGroupQuery("ident_patient",$options + array("enabled"=>1));
            $patientGroupQuery->setIncluded(true);
        }
        */
        return $runner;
    }
}