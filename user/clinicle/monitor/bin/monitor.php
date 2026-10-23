<?php
date_default_timezone_set("Europe/London");

require_once(__DIR__."/../vendor/autoload.php");

use Clinicle\Monitor\Monitor;
Monitor::register("pathload","Pathology load",array("patient"=>"JEFF BANKS"));
Monitor::state(Monitor::RUNNING);

for($i=0;$i<1000;$i++) {
	if($i%20==0) {
		Monitor::state(Monitor::WARNING);
		Monitor::warn("My warning to you ".$i);
	}
	if($i%100==0) {
		Monitor::error("My error to you ".$i);
	}
	Monitor::info("My message to you ".$i);
}
Monitor::property("status","AT POINT 1");
Monitor::handleExit(255);
