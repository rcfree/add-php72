<?php

class ADD_Page_PanelContent extends ADD_Page_AbstractContent {

	protected $_public = true;

	function isWebContent() {
		return false;
	}

	function createMenuPath() {
		return "client/panel-content";
	}
	

	
}
