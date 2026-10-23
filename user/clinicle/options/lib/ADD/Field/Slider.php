<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 30/08/16
 * Time: 14:48
 */
class ADD_Field_Slider extends ADD_Field_Text_Number {
    function addFieldMarkup($page) {
        $page->html()->addTextElement("div","",array("name"=>$this->getName()."-slider","style"=>"width:".$this->getSetting("width",400)."px;float:left;margin-right:1em;margin-top:0.7em"));
        parent::addFieldMarkup($page);
    }
    function afterAddMarkup($page)
    {
        parent::afterAddMarkup($page);
        if (ADD::sys()->hasState('readOnly') || $this->getSetting('viewonly') || $this->isReadOnly()) {
            $this->setSetting("readonly", 1);
        }
        $this->addJsValidation("SetupSlider", $page);
    }
}
?>