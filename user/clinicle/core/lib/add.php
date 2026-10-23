<?php
date_default_timezone_set("Europe/London");
use ClinicLE\System\Initialiser;
use ClinicLE\System\Service\CoreServices;

class ADD {
	private static $sys = null;
	public static $log = array();
	private static $debug = array();
	private static $prevTime=null;
	private static $prevMessage=null;
	private static $prevTimes = array();
	private static $prevMessages = array();
	private static $embedded = 0;
    private static $logConfig=false;

	/**
	 * @return \ClinicLE\System\System
	 * @throws ADD_SystemException
	 */
	static function sys() {
		//if system is not init throw an exception - should use ADD::init() initially
		//this means you can simply call ADD::sys() following initialisation
		// knowing the paths have already been set up
		if (!ADD::$sys || !is_object(ADD::$sys)) {
			throw new ADD_SystemException("ADD must be initialised first using ADD::init() method");
		}
		return ADD::$sys;
	}

	static function isInit() {
		if(!ADD::$sys || !is_object(ADD::$sys)) {
			return false;
		}
		return true;
	}
	
	static function db($type = "BASE") {
		return ADD::$sys->getService(CoreServices::DATABASE_MANAGER)->get($type);
	}

	static function dbm($dbm = null) {
		if ($dbm) {
			$serviceContainer = ADD::$sys()->getServiceContainer();
			$serviceContainer[CoreServices::DATABASE_MANAGER] = function($c) {
				return $dbm;
			};
		}
		return ADD::$sys->getService(CoreServices::DATABASE_MANAGER);
	}

	static function log($class = null) {
		$envInit = false;
		if(!self::$logConfig) {
			$logConfigFile = getenv('CLE_LOG_CONFIG');
			if(!$logConfigFile) {
				try {
					$configPath = ADD::sys()->configPath();
				} catch (ADD_SystemException $ex) {
					$configPath = getenv('ADD_PATH') . "/config/" . getenv('ADD_PROFILE');
					$envInit = true;
				}
				$logConfigFile = $configPath.'/log4php.xml';
			}
			else {
				$logConfigFile = $configPath = ADD::sys()->configPath()."/".$logConfigFile;
			}
    		Logger::configure($logConfigFile);
            self::$logConfig=true;
        }

		if (!$class) {
			$callers = debug_backtrace();
			$class = isset($callers[1]) ? ADD_Util::val('class', $callers[1], "Unknown") : "System";
		}
		if (is_object($class))
			$class = get_class($class);
		if (!isset(self::$log[$class])) {
			$log = new ADD_Tool_Logger(Logger::getLogger($class));
			self::$log[$class] = $log;
		}
		if ($envInit) {
			self::$log[$class] -> warn("ADD logging init using from env vars ($configPath)");
		}
		return self::$log[$class];
	}

	static function init(Initialiser $init) {
		
		if (!is_object($init))
			throw new ADD_SystemException("Init should be passed an ADD_InitialiserInterface implementing object");
		
		ADD::$sys = $init -> createSystem();
		
		if($init->areErrorsHandled()===true) {
			\ClinicLE\Common\ErrorHandler::init();
		}
		return ADD::$sys;
	}

	/**
	 * @return \ClinicLE\System\Application
	 */
	static function web() {
		return ADD::$sys->getApplication();
	}

	static function reset() {
		ADD::$sys = null;
		ADD::$log = null;

	}

	static function debug($message, $data = null) {
		$ex = new Exception();
            
		ADD::$debug[] = array($message, $data, $ex);
        
		if($data) {
			$message.= "\n" . print_r($data, 1);
		}
		self::log("add.php") -> debug($message);//.$ex->getTraceAsString());
	}

	static function outputDebug() {
		$output = "";
		$counter=1;
		foreach (ADD::$debug as $debug) {
			$output .= "DEBUG ".$counter++.": ".$debug[0] . "<pre>".$debug[2]->getTraceAsString()."</pre>".($debug[1] ? "<pre>" . print_r($debug[1], 1) . "</pre>" : "") . "<br/>";
		}
		return $output;
	}
	static function millitime() {
	  $microtime = microtime();
	  $comps = explode(' ', $microtime);
	
	  // Note: Using a string here to prevent loss of precision
	  // in case of "overflow" (PHP converts it to a double)
	  return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
	}
	static function logTime($message) {
		$time = self::millitime();
		
		$took = self::$prevTime ? $time - self::$prevTime : null;
		if($took) {
			self::debug("<font color='purple'>LOG TIME ".($took ? "($took ms) " :"").self::$prevMessage."</font>");
		}
		self::$prevTime = $time;
		self::$prevMessage = $message;
	}
	static function startTime($message) {
		self::debug(str_repeat("&raquo;",self::$embedded)."<font color='green'>S:$message</font>");
		
		self::$prevTimes[$message] = self::millitime();
		self::$prevMessages[$message] = $message;
		self::$embedded++;
	}
	static function endTime($message) {
		$time = self::millitime();
		
		$took = $time - self::$prevTimes[$message];
		if(self::$embedded>0) {
			self::$embedded--;
		}
		self::debug(str_repeat("&raquo;",self::$embedded)."<font color='red'>E:".self::$prevMessages[$message]." ($took ms) </font>");

		self::$prevTime = null;
		self::$prevMessage = null;
		
	}
}

?>
