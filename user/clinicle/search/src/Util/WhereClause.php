<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 20/04/17
 * Time: 11:44
 */

namespace ClinicLE\Search\Util;


use ClinicLE\Common\Util;

class WhereClause
{
    private $conditions = array();
    private $logic;
    private $params = array();

    function __construct($logic = FilterLogics::OR_LOGIC) {
        $this->logic = $logic;
    }
    function addCondition($condition,$value) {
        if(is_array($value)) {
            $this->conditions[]=$condition;
            $this->params = array_merge($this->params,$value);
            return;
        }
        $this->conditions[]=$condition;
        $this->params[]=$value;
    }
    function addSubWhereClause(WhereClause $where) {
        $this->addCondition($where->generateConditions(),$where->generateParams());
    }
    function generateConditions() {
        return "(".implode(" ".$this->logic." ",$this->conditions).")";
    }
    function generateParams() {
        return $this->params;
    }
    function hasConditions() {
        return count($this->conditions)>0;
    }
}