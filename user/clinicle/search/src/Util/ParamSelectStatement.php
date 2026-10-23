<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 11:02
 */

namespace ClinicLE\Search\Util;


use ClinicLE\Common\Statement\SelectStatement;
use ClinicLE\Search\Group\AbstractGroup;
use ClinicLE\Search\Group\AbstractPatientPageGroup;
use ClinicLE\Search\Group\EncounterPageGroup;
use ClinicLE\Search\Group\SectionGroupInterface;
use ClinicLE\Search\Query\GroupQuery;

class ParamSelectStatement extends SelectStatement
{
    private $params=array();
    private $patientJoined = false;
    private $groupJoined = array();

    function addWhereClause(WhereClause $where, $logic="AND") {
        $this->params = array_merge($this->params,$where->generateParams());
        $this->addWhere($where->generateConditions(),$logic);
    }
    function getParams() {
        return $this->params;
    }

    function isGroupJoined(AbstractGroup $group) {
        return isset($this->groupJoined[$group->getSectionType()]);
    }

    function isPatientJoined() {
        return $this->patientJoined;
    }

    function setPatientJoined($patientJoined) {
        $this->patientJoined = $patientJoined;
    }

    function addPatientJoin(GroupQuery $query) {
        $ex = new \Exception();
        if($this->patientJoined) {
            return;
        }
        $sectionsJoined = $this->groupJoined;
        $tableExpr = $query->getGroup()->getTableExpression();
        $tableAlias = $query->getTableAlias();
        if(count($sectionsJoined)==0) {
            $this->setFrom("$tableExpr $tableAlias");
            $this->patientJoined = true;
            return;
        }
        $firstSection = array_keys($sectionsJoined)[0];
        $nextAlias = $sectionsJoined[$firstSection];
        $this->addJoin($query->getJoinType()." join ".$tableExpr." "
            .$tableAlias." on "
            .$tableAlias.".id = $nextAlias.patient_id");
        $this->patientJoined = true;
    }
    function addGroupJoin(GroupQuery $query) {
        if($this->isGroupJoined($query->getGroup())) {
            return;
        }

        $group = $query->getGroup();
        $tableExpr = $query->getGroup()->getTableExpression();
        $tableAlias = $query->getTableAlias();
/*
        if(!$sectionName) {
            \CLE::info("parentPage does not have section");
            return;
        }
*/

        $joinInfo = $this->extractPreviousJoin($group, $tableAlias);
        if($joinInfo === null) {
            $this->setFrom("$tableExpr ".$tableAlias);
            return;
        }
        list($prevJoinTableAlias, $sourceJoinField, $targetJoinField) = $joinInfo;
        $this->addJoin($query->getJoinType()." join ".$tableExpr." "
            .$tableAlias." on "
            .$tableAlias.".$sourceJoinField = $prevJoinTableAlias.$targetJoinField");
    }

    function extractPreviousJoin(AbstractGroup $group, $tableAlias) {
        $sectionName = $group->getSectionName();

        $patientIdField = $group->getPatientIdField();
        $sectionIdField = $group->getSectionIdField();
        if(!isset($this->groupJoined[$sectionName])) {
            \CLE::info("sectionJoins:",$this->groupJoined);
            if(count($this->groupJoined)==0) {
                $this->groupJoined[$sectionName]=array($tableAlias, $patientIdField, $sectionIdField);
                return null;
            }

            $sectionAliases = array_values($this->groupJoined);
            $output = array_pop($sectionAliases);
            $output[2]=null;
            $this->groupJoined[$sectionName]=array($tableAlias, $patientIdField, $sectionIdField);
        }
        else {
            $output = $this->groupJoined[$sectionName];
        }
        \CLE::info("output",$output);
        if($sectionIdField === null || $output[2] === null) {
            return array($output[0],$patientIdField, $output[1]);
        }
        return array($output[0],$sectionIdField, $output[2]);
    }

}