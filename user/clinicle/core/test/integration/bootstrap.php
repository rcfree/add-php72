<?php
ini_set("opcache.validate_timestamps","on");
ini_set("opcache.revalidate_freq",0);
$home = "/Users/rcf8/Projects/ADD";

set_include_path(get_include_path().":$home/lib:$home/vendor");
require_once("ADD/Manage/Setup.php");
require_once("ADD/Manage/Load.php");
require_once ("ADD/Initialiser/Standard.php");
require_once ("add.php");
chdir($home);
putenv('ADD_PATH='.$home."/test/system");
putenv('ADD_PROFILE=local-stable');
$init = new ADD_Initialiser_Standard($home."/test/system","test-".getenv('ADD_PROFILE'));

$sys = ADD::init($init);
$sys->setCache(new ADD_Cache_None());
$actConfig = new ADD_Config();
$actConfig->loadFile("$home/config/".getenv("ADD_PROFILE")."/add.ini");
$sys->config("source_module_path","../../".$actConfig->getSetting("source_module_path"));
$sys->config("module_path",$actConfig->getSetting("module_path"));

$root = Logger::getRootLogger();
$root->setLevel(LoggerLevel::getLevelError());

$setup = new ADD_Manage_Setup();
$setup->manage(array('v'=>'2.0.0','f'=>1));

//$upgrade = new ADD_Manage_Upgrade();
//$upgrade->manage(array());

$load = new ADD_Manage_Load();
$load->manage(array("m"=>"dac","f"=>1));
chdir($home);

$root->setLevel(LoggerLevel::getLevelInfo());


?>
