<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Tool_ErrorHandler {
	public static $suppress=false;
	static function init() {
		set_error_handler(function($errno,$errstr,$errfile,$errline) {
			if ($errno==E_DEPRECATED || $errno==E_WARNING) {
		        // This error code is not included in error_reporting
		        return;
		    }
			throw new ErrorException($errstr,$errno,null,$errfile,$errline);
		});
	
		register_shutdown_function( "ADD_Tool_ErrorHandler::fatalHandler" );
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
		throw new ADD_FatalErrorException( $errstr,$errno,2,$errfile,$errline );

	}
}
class ADD_FatalErrorException extends ErrorException { }