<?php
set_include_path(get_include_path().":".getcwd()."/../lib:".getcwd()."/../module/patient/php:".getcwd()."/../module/ajax/lib");
include_once("../vendor/autoload.php");
$modules = new ADD_ModuleCollection();
$ml = new ADD_ModuleLoad("/Users/rcf8/Projects/ADD_modules");
$ml->load($modules,array("advcopd"));
$pages = $modules->get('advcopd')->getPages();
foreach($pages as $name => $page) {
    echo "page:$name\n";
}
?>