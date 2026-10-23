<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:02
 */

namespace ClinicLE\Search\SearchType;

class LinkedTextSearchType extends StringSearchType
{


    function processAttribute($row, \ClinicLE\Search\Query\GroupQuery $groupQuery)
    {
        $fieldAlias = $this->getFieldAlias($groupQuery);
        $row[$fieldAlias] = "<a onclick=\"CLE.Client.gotoNgPage('/?pid=".$row[$fieldAlias]."')\" href=\"javascript:;\">Go to patient</a>";
    }

}
