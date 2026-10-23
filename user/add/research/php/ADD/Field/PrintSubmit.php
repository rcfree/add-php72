<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/19
 * Time: 13:06
 */

class ADD_Field_PrintSubmit extends ADD_Field_Submit
{
    function getFieldAttrs($page) {
        $onClick=$this->getOnClick($page);
        $linkPath = $this->getLinkPath($page);
        $class = $this->getLinkClass($page);
        $attrs = array(
            "name"=>$this->getName(),
            "value"=>"submit",
            "class" => $class,
            "type"=>$this->getButtonType($page),
            "cle-nong"=>1
        );
        if ($onClick) $attrs['onclick']=$onClick;
        if ($linkPath) $attrs['href']=$linkPath;
        return $attrs;
    }
    function getButtonType($page) {
        return "submit";
    }
}