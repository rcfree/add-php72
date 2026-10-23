<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Common\Exception;

class FatalErrorException extends \ErrorException implements UserExceptionInterface
{
    function getUserMessage() {
        return "A fatal configuration error occured";
    }
    function isSystemAdminError() {
        return true;
    }
    function __toString() {
        return $this->outputError(false);
    }
    function outputError($as_array=false) {
        $msg = $this->getUserMessage().".";
        if($this->isSystemAdminError()) {
            $msg.=" Please contact the system administrator.";
        }

        if(\CLE::sys()->config("debug")) {
            if($as_array) {
                $msg.= "(".get_class($this).": ".$this->getExceptionMessage().")";
            }
            else {
                $msg.="<ul><li><b>Exception type:</b>".$this->getExceptionClass()."</li>";
                $msg.="<li><b>Message:</b>".$this->getExceptionMessage()."</li>";
            }
        }
        if(\CLE::sys()->config("stacktrace")) {
            $stacktrace = $this->getDebugTrace();
        }
        if($as_array) {
            return array("error_text"=>$msg,"error_trace"=>$stacktrace,"error_type"=>get_class($this));
        }
        return $msg.(isset($stacktrace) ? "<li><b>Stacktrace:</b><pre>$stacktrace</li>" : "")."</ul>";
    }

    function getDebugTrace()
    {
        return $this->getTraceAsString();
    }

    function getExceptionClass()
    {
        return get_class($this);
    }

    function getExceptionMessage()
    {
        $this->getMessage();
    }
}