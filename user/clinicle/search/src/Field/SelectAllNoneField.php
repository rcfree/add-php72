<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 02/02/17
 * Time: 14:33
 */

namespace ClinicLE\Search\Field;


class SelectAllNoneField extends \ADD_Field_Base
{
    function addMarkup($page) {
        $html = $page->html();
        $html->startElement("button",array("class"=>"btn","type"=>"button","onclick"=>htmlentities("selectAll('.content-attributes input')")));
        $html->addHtml("<i class='icon-star'></i>&nbsp;Select All");
        $html->endElement("button");
        $html->startElement("button",array("class"=>"btn","type"=>"button","onclick"=>htmlentities("unselectAll('.content-attributes input'")));
        $html->addHtml("<i class='icon-star-empty'></i>&nbsp;Select None");
        $html->endElement("button");
    }
}