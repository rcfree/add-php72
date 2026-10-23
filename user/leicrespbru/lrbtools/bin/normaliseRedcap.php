<?php
	$LRB_PATH=getenv('LRB_PATH');
	set_include_path(get_include_path().":$LRB_PATH/lib");
	require("LRB/NormaliseRC.php");
	print_r($argv);
	$studyId = isset($argv[2]) ? $argv[2] : null;
	$nr = new LRB_NormaliseRC($argv[1],$studyId);
?>