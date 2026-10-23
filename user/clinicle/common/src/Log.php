<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Common;
use Logger;

class Log {
    private static $active=false;
    private static $debug = array();
    private static $loggers = array();
    private static $start;
	private static $logConfig = false;
    static function debug($message,$data=null) {
	if(!self::$active) return;
	    
	self::$debug[] = array($message, $data);
    }
    static function isDebugOn() {
	return self::$active;
    }
    static function setDebugOn() {
	self::$active = true;
    }
    static function setDebugOff() {
	self::$active = false;
    }
    
    static function outputDebug($textOnly=false) {
	$output = "";
	foreach (self::$debug as $debug) {
		if ($textOnly) {
			$output .= $debug[0] . "\n";
			if ($debug[1]) {
				$output .= print_r($debug[1], 1) . "\n";
			}
		} else {
			$output .= $debug[0];
			if ($debug[1]) {
				$output .= "<pre>" . print_r($debug[1], 1) . "</pre>";
			}
			$output .= "<br/>";
		}
	}
	return $output;
    }

    static function info() {
		$args = func_get_args(); //self::info($errorText);
		self::logIt("info",$args);
	}
	static function error() {
		$args = func_get_args(); //self::info($errorText);
		self::logIt("error",$args);
    }
	static function warn() {
		$args = func_get_args(); //self::info($errorText);
		self::logIt("warn",$args);
    }
	static function logIt($method,$values) {
		foreach($values as $value) {
			if(!is_scalar($value)) {
				$value = print_r($value,1);
			}
			self::logger()->$method($value);
		}
	}
    static function logger($class=null) {
		$defaultLog = false;
		
	if(!self::$logConfig) {
		$configPath = getenv('ADD_PATH') . "/config/" . getenv('ADD_PROFILE');

		$defaultLog = false;
		if(file_exists($configPath . '/log4php.xml')) {
			Logger::configure($configPath . '/log4php.xml');
		}
		else {
			$defaultLog = true;
		}
		self::$logConfig=true;
	}
	$callers = debug_backtrace();
	$class = isset($callers[1]) ? $class = Util::val('class', $callers[1]) : "System";

	if (is_object($class))
		$class = get_class($class);
	if (!isset(self::$loggers[$class])) {
		$log = Logger::getLogger($class);
		if($defaultLog) {
			$log->info("Initialised default logger configuration");
		}
		else {
			$log->info("Initialised from config file ($configPath)");
		}
		self::$loggers[$class] = $log;
	}
	
	return self::$loggers[$class];
    }
	
	static function initTiming() {
		self::$start = microtime(true);
	}
	
	static function getTimeTaken() {
		return microtime(true)-self::$start;
	}
}