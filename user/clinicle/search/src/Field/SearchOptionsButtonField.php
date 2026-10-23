<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/01/17
 * Time: 11:59
 */

namespace ClinicLE\Search\Field;


class SearchOptionsButtonField extends \ADD_Field_Button
{
    function getOnClick($page)
    {
        return "var sg = CLE.Client.element('search_groups'); if(sg) {sg.scope().searchOptions(); }";
    }
    function getTitle() {
        return "<i class='icon-cog'></i>&nbsp;Search options";
    }
}