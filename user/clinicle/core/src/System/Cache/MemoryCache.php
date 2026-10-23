<?php
namespace ClinicLE\System\Cache;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\System\Exception\CacheFailException;
use ADD;

class MemoryCache {
	private $cacheData=array();
	public function get($key) {
		//ADD::debug("get:".$key);
    	if(array_key_exists($key,$this->cacheData)) {
    		//ADD::log()->info("data:".print_r($this->_cacheData[$key],1));
    		return $this->cacheData[$key];
		}
		return null;
	}
	public function put($key,$data) {
		//ADD::debug("put:".$key,$data);
		$this->cacheData[$key]=$data;
	}
	public function exists($key) {
		//ADD::log()->info("exists:".$key);
    	if (array_key_exists($key,$this->cacheData)) return true;
		return false;
	}
    public function clear() {
    	$this->cacheData=array();
    }
}