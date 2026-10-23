<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Command;
use ClinicLE\Common\Config\AbstractConfig;

abstract class AbstractCommand implements CommandInterface {
	private $runner;
	function __construct($runner) {
		$this->runner = $runner;
	}

    /**
     * @return CommandRunner
     */
	function getRunner() {
		return $this->runner;
	}
	abstract function getDescription();
    abstract function getOptsList();
    abstract function runCommand(AbstractConfig $options);
}