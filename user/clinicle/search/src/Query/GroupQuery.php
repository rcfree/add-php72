<?php

namespace ClinicLE\Search\Query;
use ClinicLE\Search\Util\FilterLogics;
use ClinicLE\Search\Group\AbstractGroup;
use ClinicLE\Common\Config\ArrayConfig;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:43
 */
class GroupQuery
{
    private $filters=array();
    private $group;
    private $attributes=array();
    private $settings;
    private $tableAlias;
    private $runner;
    private $included = false;

    /**
     * @param bool $included
     */
    public function setIncluded($included)
    {
        $this->included = $included;
    }

    /**
     * @return QueryRunner
     */
    public function getRunner()
    {
        return $this->runner;
    }

    /**
     * @return ArrayConfig
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param ArrayConfig $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param mixed $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    function addFilter($field, $operation, $value) {
        $this->filters[]=array(
            "field"=>$field,
            "operation"=>$operation,
            "value"=>$value,
        );
        $this->included = true;
    }
    function addAttribute($attribute) {
        $this->attributes[]=$attribute;
        $this->included = true;
    }
    function __construct(AbstractGroup $group, $settings = array(), QueryRunner $runner) {
        $this->group = $group;
        $this->settings = new ArrayConfig($settings);
        $this->tableAlias = $this->generateTableAlias();
        $this->runner = $runner;
    }
    function getAttributes() {
        return $this->attributes;
    }
    function addAttributes($attributes) {
        $this->attributes = array_merge($this->attributes,$attributes);
        $this->included = true;
    }
    function addAllAttributes() {
        foreach($this->getGroup()->getSearchTypes() as $ft) {
            $attributes[]=$ft->getName();
        }
        $this->addAttributes($attributes);
    }
    function isIncluded() {
        return $this->included;
    }
    function isEnabled() {
        return $this->settings->get("enabled")==1;
    }
    function getInternalLogic() {
        return $this->settings->get("internal_logic");
    }
    function getExternalLogic() {
        return $this->settings->get("external_logic");
    }
    function getJoinType() {
        return $this->settings->get("join_type");
    }
    function getTableAlias() {
        return $this->tableAlias;
    }
    function generateTableAlias() {
        $group = $this->getGroup();
        return $group->getTableExpression()."_".$group->incrementAliasCount();
    }
}