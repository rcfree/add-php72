<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 15:37
 */

namespace ClinicLE\Search\SearchType;


use ClinicLE\Common\Config\ArrayConfig;

use ClinicLE\Common\Util;
use ClinicLE\Entity\Field\DropdownField;
use ClinicLE\Entity\OptionList\DefinedOptionList;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\System\Factory\SystemFactories;

class YesNoSearchType extends ListSearchType
{

    function processAttribute($row,GroupQuery $groupQuery) {

        $fieldAlias = $this->getFieldAlias($groupQuery);
        $value = $row[$fieldAlias];
        if(Util::isEmpty($value)) {
            $value = "";
        }
        if($value=="Y") {
            $value = "1";
        }
        if($value=="N") {
            $value = "0";
        }
        $row[$fieldAlias]=$value;
    }
}