<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Event;
use Symfony\Component\EventDispatcher\Event;
/**
 * Description of PatientEvent
 *
 * @author rob
 */
class CommandEvent extends Event {
	private $options;
	
	function __construct($options) {
		$this->options = $options;
	}
	
	function getOptions() {
		return $this->options;
	}
}
