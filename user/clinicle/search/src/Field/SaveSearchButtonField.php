<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/01/17
 * Time: 11:59
 */

namespace ClinicLE\Search\Field;


use ClinicLE\Common\Util;
use ClinicLE\Search\Resource\SearchServices;

class SaveSearchButtonField extends \ADD_Field_Button
{
    function getOnClick($page)
    {
        return "var sg = CLE.Client.element('search_groups'); if(sg) {sg.scope().saveSearch(".$this->getSetting("save_new")."); }";
    }
    function getTitle() {
        return "<i class='icon-hdd'></i>&nbsp;".parent::getTitle();
    }

}