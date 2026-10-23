<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/01/17
 * Time: 11:59
 */

namespace ClinicLE\Search\Field;


class NewSearchButtonField extends \ADD_Field_Button
{
    function getFieldHtmlAttrs($page)
    {
        $attrs = parent::getFieldHtmlAttrs($page);
        $attrs['ng-click']="gotoPage('search/definition?action=create')";
        unset($attrs['disabled']);
        return $attrs;
    }
    function getTitle() {
        return "<i class='icon-plus'></i>&nbsp;New search";
    }
    function isAvailable($page)
    {
        $defPage = $page->getModule()->getPage("definition");
        return $defPage->getPermission("create");
    }
}