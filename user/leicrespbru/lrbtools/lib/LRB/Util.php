<?php
class LRB_Util {
	
	static function dmyToYMD($date) {
		$dt = DateTime::createFromFormat("d/m/Y",$date,new DateTimeZone(date_default_timezone_get()));
		return $dt->format("Y-m-d");
	}
}

?>