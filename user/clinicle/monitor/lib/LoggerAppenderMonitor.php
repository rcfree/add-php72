<?php

use Clinicle\Monitor\Monitor;
 
class LoggerAppenderMonitor extends LoggerAppender {
    private $actionName;
    private $actionDescription;
    private $monitorHost;
    private $failoverLog;
    
    public function activateOptions() {
        global $argv;
        $params = array(
            "monitorHost"=>$this->getMonitorHost(),
            "failoverLog"=>$this->getFailoverLog()
        );

        Monitor::configure($params);
        Monitor::register($this->getActionName(), $this->getActionDescription(),$argv);
    }
    public function append(LoggerLoggingEvent $event) {
       if($event->getLevel()=="FATAL") {
           Monitor::failed($event->getMessage());
       } 
        $matches = array();
        
       $message = $event->getMessage();
       if(is_array($message) && count($message)==1) {
           $message = $message[0];
       }
       else {
           $message = $event->getRenderedMessage();
       }
        if(preg_match("/^STATE\:(.+)/",$message,$matches)) {
            Monitor::state($matches[1]);
            return;
        }
        if(preg_match("/^EXIT\:(.+)/",$message,$matches)) {
            Monitor::handleExit($matches[1]);
            return;
        }
        else {
           Monitor::log($message,$event->getLevel());
        }
    }
    public function requiresLayout() {
        return false;
    }
    public function close() {
    }
    
    public function getActionName() {
        return $this->actionName;
    }
    public function setActionName($actionName) {
        $this->actionName = $actionName;
    }
    
    public function getActionDescription() {
        return $this->actionDescription;
    }
    public function setActionDescription($actionDescription) {
        $this->actionDescription = $actionDescription;
    }
    
    public function getFailoverLog() {
        return $this->failoverLog;
    }
    public function setFailoverLog($failoverLog) {
        $this->failoverLog = $failoverLog;
    }
    public function getMonitorHost() {
        return $this->monitorHost;
    }
    public function setMonitorHost($monitorHost) {
        $this->monitorHost = $monitorHost;
    }
}
