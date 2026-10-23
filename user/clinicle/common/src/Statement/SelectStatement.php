<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/09/16
 * Time: 13:08
 */
namespace ClinicLE\Common\Statement;

class SelectStatement
{
    private $select = array();
    private $from;
    private $joins = array();
    private $wheres = array();
    private $limit;
    private $orderBys = array();
    private $distinct=false;
    private $groupBy = array();
    private $having;

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @param mixed $having
     */
    public function setHaving($having)
    {
        $this->having = $having;
    }
    function addGroupBy($groupBy) {
        $this->groupBy[] = $groupBy;
    }
    function setSelect($select) {
        $this->select=$select;
    }
    function addToSelect($select) {
        $this->select[]=$select;
    }
    function setFrom($from) {
        $this->from=$from;
    }
    function addJoin($join) {
        $this->joins[]=$join;
    }
    function getJoins() {
        return $this->joins;
    }
    function addWhere($where, $logic="AND") {
        $this->wheres[]=array($where, $logic);
    }
    function addOrderBy($orderBy) {
        $this->orderBys[]=$orderBy;
    }
    function setLimit($limit) {
        $this->limit = $limit;
    }

    /**
     * @deprecated Replaced with generateFullSql
     * @return string
     */
    function generateSql() {
        return $this->generateFullSql();
    }
    private function generateFromClause($countOnly=false) {
        $sql = "from ".$this->from;

        $sql.=" ".implode(" ",$this->joins);

        $wheres = array();
        $prevWhere = array("","");
        foreach($this->wheres as $where) {
            if(is_array($where[0])) {
                $where[0]="(".implode(" ".$where[1]." ",$where[0]).")";
            }

            if (count($wheres) == 0) {
                $wheres[] = $where[0];
            } else {
                $wheres[] = $prevWhere[1] . " " . $where[0];
            }

            $prevWhere = $where;
        }
        if(count($wheres)>0) {
            $sql.=" where ".implode(" ",$wheres);
        }
        if(count($this->groupBy)>0) {
            $sql.=" group by ".implode(",",$this->groupBy);
        }
        if($this->having) {
            $sql.=" having ".$this->having;
        }
        if(!$countOnly && count($this->orderBys)) {
            $sql .= " order by " . implode(",", $this->orderBys);
        }
        if(!$countOnly && $this->limit) {
            $sql.=" limit ".$this->limit;
        }
        return $sql;
    }
    function generateFullSql($countOnly=false) {
        $select = is_array($this->select) ? implode(",",$this->select) : $this->select;

        $sql = "select ".($this->isDistinct() ? "distinct " : "").$select." ".$this->generateFromClause($countOnly);
        \CLE::debug("full sql:".$sql);
        return $sql;

    }
    function generateCountSql() {
        //if($this->isDistinct()) {
        $sql = "select count(*) from (".$this->generateFullSql(true).") as temp";
        //}
        //else {
        //    $sql = "select count(*) ".$this->generateFromClause(true);
        //}
        \CLE::debug("count sql:".$sql);
        return $sql;

    }
    function __toString() {
        return $this->generateSql();
    }

    /**
     * @return bool
     */
    public function isDistinct()
    {
        return $this->distinct;
    }

    /**
     * @param bool $distinct
     */
    public function setDistinct($distinct)
    {
        $this->distinct = $distinct;
    }

}