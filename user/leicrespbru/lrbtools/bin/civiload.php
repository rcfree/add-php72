<?php
if (!getenv('LRB_PATH')) die("'LRB_PATH' env variable not set");
set_include_path(getenv('LRB_PATH')."/lib");
date_default_timezone_set('UTC');

require_once("LRB/InitCivi.php");

$config = LRB_InitCivi::init(getenv('LRB_PATH'),'/Library/WebServer/Documents/civicrm');

require_once("LRB/CiviTool/EntityLoader.php");
require_once("LRB/DataFile/Excel.php");

if (empty($argv[1])) exit("ERROR: You did not provide the name of an Excel file containing the CiviLoader template\n\n");
$reverse=false;
if (!empty($argv[2])) $reverse=true;
if ($reverse) echo "REVERSING THE PROCESS\n";
$dataFile = new LRB_DataFile_Excel($argv[1]);
LRB_CiviTool_EntityLoader::run($dataFile,$reverse);
