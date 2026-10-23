<?php
namespace ClinicLE\System\Cache;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\System\Exception\CacheFailException;
use ADD;

class NoCache {
	private $memCached;
	private $log;
	
	function exists($key) {
		return false;
	}
	function get($key) {
		return null;
	}
	function put($key,$value) {
		
	}
	function clear() {
		
	}
}