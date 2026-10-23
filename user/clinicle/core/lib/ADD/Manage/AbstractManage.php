<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class ADD_Manage_AbstractManage implements ADD_ManageInterface {
	private $runner;
	function __construct($runner) {
		$this->runner = $runner;
	}
	function getRunner() {
		return $this->runner;
	}
	abstract function getDescription();
    abstract function getOptsList();
    abstract function manage($options);
}