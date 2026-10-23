<?php
namespace EMBER\Blness\Field;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 19/04/17
 * Time: 10:26
 */
class EmberNumberField extends \ADD_Field_Text
{
    function beforeAddMarkup($page) {
        $add = \ADD::sys();
        $this->setSetting('size',7);
        if ($add->hasState('readOnly')) return;
        if ($this->isReadOnly() || $this->getSetting('viewonly')) return;
        $this->addJQuery($page,'mask',"'aaa9999'");
    }
}