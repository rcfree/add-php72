<?php
namespace ClinicLE\Options\HomeTool;
use CLE;

abstract class AbstractPrivateHomeTool extends AbstractHomeTool {
	public function isAvailable() {
		if(!CLE::sys()->getAccessManager()->isLoggedIn()) return false;
		return $this->isAvailableToUser();
	}
	abstract public function isAvailableToUser();
}
