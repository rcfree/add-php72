<?php

class ADD_Cache_Memory implements ADD_CacheInterface {
	private $_cacheData=array();
	public function get($key) {
		ADD::debug("get:".$key);
    	if(array_key_exists($key,$this->_cacheData)) {
    		//ADD::log()->info("data:".print_r($this->_cacheData[$key],1));
    		return $this->_cacheData[$key];
		}
		return null;
	}
	public function put($key,$data) {
		ADD::debug("put:".$key,$data);
		$this->_cacheData[$key]=$data;
	}
	public function exists($key) {
		//ADD::log()->info("exists:".$key);
    	if (array_key_exists($key,$this->_cacheData)) return true;
		return false;
	}
    public function clear() {
    	$this->_cacheData=array();
    }
}
?>