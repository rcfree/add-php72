<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 15:37
 */

namespace ClinicLE\Search\SearchType;


use ClinicLE\Common\Config\ArrayConfig;

use ClinicLE\Entity\Field\DropdownField;
use ClinicLE\Entity\OptionList\DefinedOptionList;
use ClinicLE\System\Factory\SystemFactories;

class BooleanSearchType extends AbstractSearchType
{

    function createRenderField($name,$title, $operation)
    {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);
        $field = $rowFieldFactory->newInstance(array("Type"=>"dropdown","Field"=>$name,"Title"=>$title,"Options"=>array("0"=>"0 [Not checked]","1"=>"1 [Checked]")));
        return $field;
    }

    function getOptions() {
        return $this->options;
    }

}