<?php

date_default_timezone_set('Europe/London');
$autoLoadFile = __DIR__.'/../vendor/autoload.php';
if(!file_exists($autoLoadFile)) {
	throw new Exception("Composer autoload file '$autoLoadFile' does not exist");
}
include_once $autoLoadFile;
