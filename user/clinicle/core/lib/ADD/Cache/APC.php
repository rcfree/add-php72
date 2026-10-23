<?php

class ADD_Cache_APC implements ADD_CacheInterface {
    public function __construct() {
        if(!function_exists("apc_fetch")) {
            throw new ADD_SystemException("APC caching library not installed on system");
        }
    }
    public function get($key) {
            //ADD::log()->info("get:$key");
            return apc_fetch($key);
    }
    public function put($key,$data) {
            //ADD::log()->info("put:$key,".json_encode($data));
            apc_store($key,$data);
    }
    public function exists($key) {
            //ADD::log()->info("exists:$key");
            return apc_exists($key);
    }
    public function clear() {
        apc_clear_cache("user");
    }
}
?>