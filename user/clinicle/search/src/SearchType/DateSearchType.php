<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:02
 */

namespace ClinicLE\Search\SearchType;

use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Entity\Field\DateField;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\System\Service\Locale;

class DateSearchType extends AbstractSearchType
{
    private $ISODate=false;

    function __construct($field, $fieldTitle=null,$isoDate=false)
    {
        parent::__construct($field,$fieldTitle);
        $this->ISODate = $isoDate;
    }

    /**
     * @return boolean
     */
    public function isISODate()
    {
        return $this->ISODate;
    }

    /**
     * @param boolean $ISODate
     */
    public function setISODate($ISODate)
    {
        $this->ISODate = $ISODate;
    }

    function createRenderField($name,$title, $operation) {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        $field = $rowFieldFactory->newInstance(array("Field"=>$name,"Title"=>$title,"Type"=>"Date","Settings"=>array("datepicker"=>true)));

        return $field;
    }

    function getOperations() {
        $ops = parent::getOperations();
        $ops += array("gt"=>"greater than","ge"=>"greater or equal","lt"=>"less than","le"=>"less than or equal","bt"=>"between");
        return $ops;
    }

    function getFilterValues($filter) {
        $locale = \CLE::svc(CoreServices::LOCALE);
        $oldValues = Util::val("value",$filter,array());
        $newValues = array_map(function($dmy) use($locale) { return $locale->displayDateToISO($dmy); },$oldValues);
        return $newValues;
    }

    function createObsFieldSubQuery($joinAlias, $sourceDbName) {
         return "(select str_to_date(o.value,'%d/%m/%Y') from {$sourceDbName}.observation o left join {$sourceDbName}.observation_field of on o.observation_field_id=of.id where o.encounter_id=`$joinAlias`.id and of.name='".$this->getFieldName()."')";
    }

    function getFieldSelectExpression(GroupQuery $groupQuery)
    {
        return "DATE_FORMAT(".$groupQuery->getTableAlias().".".$this->getFieldName().",'%d/%m/%Y')";
    }
    function processAttribute($row, \ClinicLE\Search\Query\GroupQuery $groupQuery)
    {
        $fieldAlias = $this->getFieldAlias($groupQuery);
        $cell = $row[$fieldAlias];
        if ($cell=="00/00/0000") {
            $row[$fieldAlias] = "NULL";
            return;
        }
    }
}