<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Common\Connection;

/**
 * Description of StandardConnection
 *
 * @author rcf8
 */
class StandardConnection extends AbstractConnection {
    protected $dsn;
    
	public function __construct($dsnOrSettings, $user=null, $pass=null, $options=array()) {
		if(is_object($dsnOrSettings)) {
			$this->dsn = $dsnOrSettings->get("dsn");
			$this->user = $dsnOrSettings->get("user");
			$this->pass = $dsnOrSettings->get("pass");
			$this->options = $dsnOrSettings->get("options");
		}
		else {
			$this->dsn = $dsnOrSettings;
			$this->user = $user;
			$this->pass = $pass;
			$this->options = $options;
		}
                $this->populateFromDSN();
	}
}
