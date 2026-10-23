<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:02
 */

namespace ClinicLE\Search\SearchType;


use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Entity\Field\MemoField;
use ClinicLE\Entity\Field\TextField;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Search\Util\FilterLogics;
use ClinicLE\System\Factory\SystemFactories;

class CustomQuerySearchType extends AbstractSearchType
{
    function getOperations()
    {
        return array(
            "eq"=>"where"
        );
    }
    function createRenderField($name,$title, $operation) {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);
        $field = $rowFieldFactory->newInstance(array("Field"=>$name,"Title"=>"Query","Type"=>"Memo","Settings"=>array("size"=>80,"height"=>6)));
        return $field;
    }

}