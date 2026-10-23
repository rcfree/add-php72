<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 21:33
 */

namespace ClinicLE\Search\Output;


class DefinitionOutput extends AbstractOutput
{
    function outputResponse()
    {
        $runner = $this->getRunner();
        $groupData = array();
        $groupId = 1;
        foreach($runner->getGroupQueries() as $groupQuery) {
            $group = $groupQuery->getGroup();
            $filters = $groupQuery->getFilters();
            $filterOutput = array();
            foreach($filters as $filter) {
                $filter['values']=$filter['value'];
                unset($filter['value']);
                $filterOutput[]=$filter;
            }
            $groupData[]=array(
                "id"=>$groupId,
                "title"=>$group->getName(),
                "name"=>$group->getName(),
                "filters"=>$filterOutput,
                "attributes"=>$groupQuery->getAttributes(),
                "settings"=>$groupQuery->getSettings()->getSettings()
            );
            $groupId++;
        }
        return array("groups"=>$groupData);
    }
}