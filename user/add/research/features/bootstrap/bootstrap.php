<?php
global $home;
$home = getenv("ADD_PATH");
set_include_path("$home/lib");
require_once ("add.php");
$add = ADD::init($home);
$user = new stdClass();
$user->username="testuser";
$add->setUser($user);
?>
