<?php

date_default_timezone_set('Europe/London');

$autoloadPath = getenv("CLE_AUTOLOAD_PATH");
include_once ($autoloadPath ? : "__DIR__.'/../vendor")."/autoload.php";