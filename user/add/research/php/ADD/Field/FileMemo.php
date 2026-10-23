<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 19/10/17
 * Time: 10:32
 */

class ADD_Field_FileMemo extends ADD_Field_Memo
{
    function getValue()
    {
        return file_get_contents(CLE::sys()->getEnvironment()->getConfigPath()."/files/".$this->getSetting("filename"));
    }
}