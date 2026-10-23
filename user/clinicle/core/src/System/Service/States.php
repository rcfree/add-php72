<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\System\Service;

class States {
	private $states=array();
	
	function hasState($state) {
		return isset($this->states[$state]) ? $this->states[$state] : null;
	}
	function setState($state,$flag) {
		$this->states[$state]=$flag;
	}
}