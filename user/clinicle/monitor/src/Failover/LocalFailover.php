<?php
namespace Clinicle\Monitor\Failover;
use ClinicLE\Common\Util;
use ClinicLE\Service\Email\Emailer;
use ClinicLE\Service\Email\Email;

class LocalFailover {
	private $enabled = false;
	private $logFilename;
	private $logHandle;
	
	function __construct($action, $logRoot) {
		$this->action = $action;
		$this->logRoot = $logRoot;
	}
	function getLogFilename() {
		return $this->logRoot."/".$this->logFilename;
	}
	function isEnabled() {
		return $this->enabled;
	}
	function enable() {
		if($this->enabled==true) {
			return;
		}
		$action = $this->action;
		
		if(!$this->logFilename) {
			$this->logFilename = $action->getUuid() ? : uniqid("MON-");
		}
		$this->notify("ERROR remote monitor not available","Monitor log is being redirected to: ".$this->getLogFilename()." for action ".$action->getDetails());
		$this->log("notify","error","User notified of monitor system unavailable for action ".$action->getDetails());
		$this->enabled = true;
	}
	
	function disable($monitor) {
		if($this->enabled===true) {
			$this->log("disable","info","Monitor system is now available. Failover log is available here:".$this->getLogFilename());
			$this->notify("SUCCESS remote monitor now available","Monitoring restored for action ".$this->action->getDetails());
		}
		$this->enabled = false;
	}
	
	function notify($subject, $body) {
		$msg = new Email("[monitor-client] ".$subject,"lrbru-monitor@uhl-tr.nhs.uk","LRBRU Monitor");
		$msg->setBody($body);
		$emailer = new Emailer();
		$emailer->sendTo($msg,"robert.free@uhl-tr.nhs.uk");
	}
	
	function log($operation, $message, $level) {
		if(!$this->logHandle) {
			$this->logHandle = fopen($this->getLogFilename(),"a");
		}
		fwrite($this->logHandle,$operation.": [$level] ".$message."\n");
	}
	
	function getDetails() {
		return "LocalFailover";
	}
	function __destruct() {
		if(!$this->logHandle) {
			return;
		}
		fclose($this->logHandle);
	}
}