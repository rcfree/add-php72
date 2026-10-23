<?php
namespace Clinicle\Monitor\ActionType;
use ClinicLE\Common\Util;

class ProcessActionType {
	private $uuid;
	
	function __construct() {
		$this->node = gethostname();
		$this->id = getmypid();
		$this->user = get_current_user();
	}
	function setType($type) {
		$this->type = $type;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function getNode() {
		return $this->node;
	}
	function getType() {
		return $this->type;
	}
	function getDescription() {
		return $this->description;
	}
	function getId() {
		return $this->id;
	}
	function getParams() {
		return $this->params;
	}
	function setParams($params) {
		$this->params = $params;
	}
	function getUser() {
		return $this->user;
	}
	function getUuid() {
		return $this->uuid;
	}
	function setUuid($uuid) {
		$this->uuid = $uuid;
	}
	function getDetails() {
		return $this->getUuid()." : ".$this->getType()." (".$this->getId().") on ".$this->getUser()."@".$this->getNode()." with ".json_encode($this->getParams());
	}
}
