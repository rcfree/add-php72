<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 17/10/17
 * Time: 21:00
 */

namespace ADD\PR\Field;


class CollapsibleFieldsetField extends \ADD_Field_Container
{
    function startFieldHtml($page) {
        parent::startFieldHtml($page);
        $html = $page->html();
        $attrs = array();
        if (!$this->getSetting('compress')) $attrs['class']="full";

        $html->startElement("fieldset",$attrs);

        $html->startElement("legend");

        $html->addHtml($this->getTitle());

        if ($this->getSetting('legend_suffix')) {
            $html->addHtml($this->getSetting('legend_suffix'));
        }

        $html->endElement("legend");

        $html->startElement("div");
    }
    function endFieldHtml($page) {
        $page->html()->endElement("div");
        $page->html()->endElement("fieldset");
        parent::endFieldHtml($page);
    }
}