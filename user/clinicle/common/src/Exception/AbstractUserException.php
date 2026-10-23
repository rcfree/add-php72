<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/03/2017
 * Time: 10:08
 */

namespace ClinicLE\Common\Exception;


abstract class AbstractUserException extends \Exception implements UserExceptionInterface
{
    function getUserMessage() {
        return $this->getMessage();
    }
    function isSystemAdminError() {
        return false;
    }
    function outputError($as_array=false) {
        $msg = $this->getUserMessage().".";
        if($this->isSystemAdminError()) {
            $msg.=" Please contact the system administrator if this happens again.";
        }
        $debug = \CLE::sys()->config("debug");
        if($debug) {
            if($as_array) {
                $msg.= "(".get_class($this).": ".$this->getExceptionMessage().")";
            }
            else {
                $msg.="<ul><li><b>Exception class:</b>".$this->getExceptionClass()."</li>";
                $msg.="<li><b>Message:</b>".$this->getExceptionMessage()."</li>";
            }
        }
        if(\CLE::sys()->config("stacktrace")) {
            $stacktrace = $this->getDebugTrace();
        }
        if($as_array) {
            $output = array("error_text"=>$msg);
            if($debug) {
                $output["error_trace"] = $stacktrace;
                $output["error_type"] = $this->getExceptionClass();
            }
            return $output;
        }
        return $msg.(isset($stacktrace) ? "<li><b>Stacktrace:</b><pre>$stacktrace</li>" : "")."</ul>";
    }
    function getDebugTrace() {
        return $this->getTraceAsString();
    }
    function getExceptionClass() {
        return get_class($this);
    }
    function getExceptionMessage()
    {
        return $this->getMessage();
    }
    function __toString() {
        return $this->outputError(false);
    }
}