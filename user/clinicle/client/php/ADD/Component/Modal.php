<?php

class ADD_Component_Modal extends ADD_Component {
	const PAGE_POST="__page";
	const MODULE_POST="__module";
	const ACTION_POST="__action";
	const ID_POST="__id";
	const PARENT_POST="__parent";
	
	const PAGE_GET="p";
	const MODULE_GET="m";
	const ACTION_GET="a";
	const ID_GET="id";
	const PARENT_GET="parentid";
	
	protected $_modalModule;
	protected $_modalPage;
	protected $_formPage;
    
	function viewFile(&$viewFile) {
		$viewFile="module/clinicle/client/bare";
	}
}
