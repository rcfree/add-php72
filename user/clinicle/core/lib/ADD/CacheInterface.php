<?php
interface ADD_CacheInterface {
	public function get($key);
	public function put($key,$data);
	public function exists($key);
        public function clear();
}
?>