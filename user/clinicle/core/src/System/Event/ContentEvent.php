<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 5/6/15
 * Time: 8:38 PM
 */

namespace ClinicLE\System\Event;

class ContentEvent extends \Symfony\Component\EventDispatcher\Event {
    private $content;
	
	function getContent() {
		return $this->content;
	}

	function setContent($content) {
		$this->content = $content;
	}

	function __construct($content) {
	    $this->content = $content;
	}
}