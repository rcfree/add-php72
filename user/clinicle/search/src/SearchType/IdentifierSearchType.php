<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:02
 */

namespace ClinicLE\Search\SearchType;

use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Search\Util\ParamSelectStatement;

class IdentifierSearchType extends AbstractSearchType
{
    function createRenderField($name,$title, $operation) {
        $field = new StringField();
        $field->setName($name);
        $field->setTitle($title);
        $field->setSettings(new ArrayConfig(array()));
        return $field;
    }
}