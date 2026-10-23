<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Install\Event;
use Symfony\Component\EventDispatcher\Event;

class PageInstallEvent extends Event {
    private $page;

	function __construct($page) {
	    $this->page = $page;
	}
	function getPage() {
		return $this->page;
	}

	function setPage($page) {
		$this->page = $page;
	}
}