<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Common;
use ClinicLE\Common\Exception\FatalErrorException;
use CLE;

class ErrorHandler {
    public static $log=true;
    static function init() {
        set_error_handler(function($errno,$errstr,$errfile,$errline) {
            if ($errno==E_DEPRECATED) {
                // This error code is not included in error_reporting
                return;
            }
            $ex = new \ErrorException($errstr,$errno,null,$errfile,$errline);

            if(self::$log) {
                $errText = "Unexpected error: $errstr \n error no: $errno, file: $errfile, line: $errline\nstacktrace:"
                    .$ex->getTraceAsString();
                if(class_exists("Logger")) {
                    CLE::log("ErrorHandler")->error($errText);
                }
                else {
                    error_log($errText);
                }
            }
            throw $ex;
        });

        register_shutdown_function( ErrorHandler::class."::fatalHandler" );
    }
    static function fatalHandler() {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;
        $error = error_get_last();


        if( $error === NULL) return;
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        //PHP sort error which needs to be suppressed
        if(substr($errstr,0,5)=="usort") {
            return;
        }
        $ex = new FatalErrorException($errstr,$errno,2,$errfile,$errline );
        if(self::$log) {
            $errText = "Unexpected error: ".$errstr."\n".print_r($error,1)."\nstacktrace:"
                .$ex->getTraceAsString();
            if(class_exists("Logger")) {
                CLE::log("ErrorHandler")->error($errText);
            }
            else {
                error_log($errText);
                //echo($errText);
            }
        }
        print "An unexpected system error occurred.<br/> ".$errText;
    }
}