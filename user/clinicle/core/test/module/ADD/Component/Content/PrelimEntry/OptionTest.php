<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/core/test/module");
require_once ("ADD/Component/Content/EncounterEntry/OptionTest.php");

class ADD_Component_Content_PrelimEntry_OptionTest extends ADD_Component_Content_EncounterEntry_OptionTest {
	protected $expectedERFunction="record";
	protected $sectionExistPermission="edit";	

}