<?php
namespace ClinicLE\System\Cache;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\System\Exception\CacheFailException;
use ADD;

class MCCache {
	const PERSISTENT_ID="clinicle";
	private $memCached;
	private $log;
	function __construct(AbstractConfig $config=null) {
		if(!class_exists("\Memcached")) {
			throw new CacheFailException("MCCache problem. Memcached PHP bindings not installed or enabled");
		}
		
		if(!$config) {
			$config = new ArrayConfig(array());
		}
		$this->config = $config;
		$this->memCached = new \Memcached($this->config->getSetting('persistent_id',self::PERSISTENT_ID));
		$serverList = $this->memCached->getServerList();
		if(count($serverList)>0) {
			return;
		}
		$this->memCached->addServer($this->config->getSetting('host','localhost'),$this->config->getSetting('port',11211));
		
	}
	function exists($key) {
		$this->memCached->get($key);
		if($this->memCached->getResultCode()==\Memcached::RES_NOTFOUND) {
			return false;
		}
		return true;
	}
	function get($key) {
		//ADD::debug("get:$key");
		return $this->memCached->get($key);
	}
	function put($key,$value) {
		//ADD::debug("put:$key");
		
		$this->memCached->set($key,$value);
		if($this->memCached->getResultCode()==\Memcached::RES_SUCCESS) {
			return;
		}
		throw new CacheFailException("Problem putting '$key' value:".$this->memCached->getResultMessage());
	}
	function clear() {
		$this->memCached->flush();
	}
}