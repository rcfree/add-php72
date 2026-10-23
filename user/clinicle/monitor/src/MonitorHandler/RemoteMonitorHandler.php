<?php
namespace Clinicle\Monitor\MonitorHandler;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;

class RemoteMonitorHandler {
	function __construct($url, $action, $failover) {
		$this->client = new Client($url);
		$this->action = $action;
		$this->failover = $failover;
	}
		
	function register() {
		$action = $this->action;
		$query = array("id"=>$action->getId(),"node"=>$action->getNode(),"user"=>$action->getUser(),"type"=>$action->getType(),"description"=>$action->getDescription(),"params"=>json_encode($action->getParams()));
		try {
			$jsonData = $this->get("/register",$query);
			$action->setUuid($jsonData['uuid']);
		}
		catch(\Exception $ex) {
			$this->failover->enable();
			$this->failover->log("register","error","Could not register action with monitor");
			return;
		}
		$this->failover->disable($this);
	}
	
	function state($state) {
		$action = $this->action;
		$query = array("state"=>$state);
		try {
			return $this->get("/state/".$action->getUuid(),$query);
		}
		catch(\Exception $ex) {
			$this->failover->enable();
			$this->failover->log("state","error","Could not update state to '$state'");
			return;
		}
		$this->failover->disable($this);
	}
		
	function log($message, $level) {
		$action = $this->action;
		$query = array("message"=>$message,"level"=>$level);
		try {
			$this->get("/log/".$action->getUuid(),$query);
		}
		catch(\Exception $ex) {
			$this->failover->enable();
			$this->failover->log("log",$level,$message);
			return;
		}
		$this->failover->disable($this);
	}
	
	function get($url,$query=array()) {
		$request = $this->client->get($url, array(),array(
			"query"=>$query
			)
		);
		
		$request->send();
		
		$response = $request->getResponse();
		
		$rawData = $response->getBody(true);
		$jsonData = json_decode($rawData,true);
		return $jsonData;
	}
	function properties() {
		$action = $this->action;
		try {
			return $this->get("/properties/".$action->getUuid());
		}
		catch(\Exception $ex) {
			$this->failover->enable();
			$this->failover->log("property","info","Get properties");
			return;
		}
		$this->failover->disable($this);
	}
	function property($name,$value) {
		$action = $this->action;
		$query = array("value"=>$value);
		try {
			return $this->get("/property/".$action->getUuid()."/$name",$query);
		}
		catch(\Exception $ex) {
			$this->failover->enable();
			$this->failover->log("property","info","Set property '".$name."' to '".$value."'");
			return;
		}
		$this->failover->disable($this);
	}
	
	function getDetails() {
		return "RemoteMonitorHandler";
	}
}


