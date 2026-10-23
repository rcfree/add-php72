<?php
if (!getenv('LRB_PATH')) die("'LRB_PATH' env variable not set");
set_include_path(getenv('LRB_PATH')."/lib");
date_default_timezone_set('UTC');

require_once("LRB/InitCivi.php");

$config = LRB_InitCivi::init(getenv('LRB_PATH'),'/var/www/htdocs/civicrm');

require_once("LRB/CiviTool/CaseLoader.php");

if (empty($argv[1])) exit("ERROR: You did not provide the name of a case XML file\n\n");
$reverse=false;
if (!empty($argv[2])) $reverse=true;
if ($reverse) echo "REVERSING THE PROCESS\n";

LRB_CiviTool_CaseLoader::run($argv[1],$reverse);
