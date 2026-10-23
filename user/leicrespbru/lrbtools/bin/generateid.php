<?php
$ctpath = getenv('CT_PATH');
set_include_path("{$ctpath}/lib");
date_default_timezone_set('UTC');

require_once("LRB/CiviTool/InitCivi.php");
$drupalpath = "/local/drupal";
 		
$config = LRB_CiviTool_InitCivi::init($drupalpath);

require_once("LRB_CiviTool_IdGenerator.php");
$dsn = $config->dsn;

$idGen = new LRBRUTool_IdGenerator($dsn,true);
for($i=1;$i<1000;$i++) {
	echo "id $i".$idGen->generateId()."\n";
}