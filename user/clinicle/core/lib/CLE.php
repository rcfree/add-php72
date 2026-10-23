<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CLE
 *
 * @author rob
 */

class CLE extends ADD {
	static function svc($serviceName) {
		return ADD::sys()->getService($serviceName);
	}
	
	static function logAtLevel($level,$args) {
		$info = array();
		foreach($args as $arg) {
			if(!is_scalar($arg)) {
				$arg = print_r($arg,1);
			}
			$info[]=$arg;
		}
        $callers = debug_backtrace();
        $class = isset($callers[2]) ? ADD_Util::val('class',                $callers[2], "Unknown") : "System";
        self::log($class)->log($level,implode(" ",$info));
	}
	
	static function info() {
		self::logAtLevel(LoggerLevel::getLevelInfo(),func_get_args());
	}
	static function warn() {
		self::logAtLevel(LoggerLevel::getLevelWarn(),func_get_args());
	}
	static function error() {
		self::logAtLevel(LoggerLevel::getLevelError(),func_get_args());
	}
	static function trace() {
		self::logAtLevel(LoggerLevel::getLevelTrace(),func_get_args());
	}
	static function debugStack($message) {
	    $ex = new \Exception("");
        CLE::info("$message".$ex->getTraceAsString());
    }
}
