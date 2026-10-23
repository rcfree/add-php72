<?php
namespace Clinicle\Monitor;
use ClinicLE\Common\Util;

class Monitor {
    private static $configured = false;
    private static $action;
    private static $handler;
    private static $registered = false;
    private static $failover;
    
    const INIT="INIT";
    const RUNNING="RUNNING";
    const FATAL="FATAL";
    const FAILED="FAILED";
    const SUCCESS="SUCCESS";
    const PROBLEM="PROBLEM";
    const WARNING="WARNING";
    const ERROR="ERROR";
    const PENDING="PENDING";
    const IN_PROGRESS="IN_PROGRESS";
    
    static function configure($configureOptions=array()) {
        if (self::$configured) {
            return;
        }
        $monitorHost = Util::val('monitorHost',$configureOptions);
        $failoverLog = Util::val('failoverLog',$configureOptions);
        
        
        self::$action = Util::val('action',$configureOptions,new ActionType\ProcessActionType());
        self::$failover = Util::val('failover',$configureOptions,new Failover\LocalFailover(self::$action,"/local/tmp/monitor"));
        self::$handler = Util::val('monitor',$configureOptions,new MonitorHandler\RemoteMonitorHandler($monitorHost,self::$action,self::$failover));
        
        set_error_handler("ClinicLE\\Monitor\\Monitor::errorHandler");
    
        register_shutdown_function( "ClinicLE\\Monitor\\Monitor::shutdownHandler" );
        self::$configured=true;
    }
    static function register($actionType, $actionDescription, $actionParams=array()) {
        if(self::$registered) {
            return;
        }   
        self::configure();
        self::$action->setType($actionType);
        self::$action->setDescription($actionDescription);
        self::$action->setParams($actionParams);
        
        self::$handler->register();
        
        self::$registered=true;
    }
    static function preventUnregistered() {
        if(!self::$registered) {
            throw new \Exception("An action to monitor has not been registered");
        }
        if(!self::$action->getUuid()) {
            if(!self::$failover->isEnabled()) {
                throw new \Exception("Action not registered even with failover disabled");  
            }
            self::$handler->register();
        }
    }
    
    static function state($state) {
        self::preventUnregistered();
        self::$handler->state($state);
    }
    static function properties() {
        self::preventUnregistered();
        return self::$handler->properties();
    }
    
    static function log($message,$level) {
        self::preventUnregistered();
        self::$handler->log($message,$level);
    }
    
    static function failed($message) {
        self::preventUnregistered();
        self::$handler->log($message,"FATAL");
        self::$handler->state(Monitor::FAILED);
    }
    
    static function property($name,$value) {
        self::preventUnregistered();
        self::$handler->property($name,$value);
    }
    
    static function shutdownHandler() {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;
        if(isset($php_errormsg)) {
            error_log($php_errormsg);
        }
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
        self::$handler->log("Action ended with error: ".$errstr,"FATAL");
        self::$handler->state(Monitor::FAILED);
    }   
    
    static function errorHandler($errno,$errstr,$errfile,$errline) {
        if ($errno==E_DEPRECATED || $errno==E_STRICT) {
            // This error code is not included in error_reporting
            return;
        }
        echo "errorHandler:".$errstr."\n";
        Monitor::failed($errstr);
        throw new \ErrorException($errstr,$errno,null,$errfile,$errline);
    }
    
    static function handleExit($exitCode=0) {
        if($exitCode>0) {
            self::$handler->log("Action exit with failed exit code: ".$exitCode,"FATAL");
            self::$handler->state(Monitor::FAILED);
        }
        else {
            self::$handler->log("Action exit with successful exit code: ".$exitCode,"INFO");
            self::$handler->state(Monitor::SUCCESS);
        }
        exit($exitCode);
    }
            
}

