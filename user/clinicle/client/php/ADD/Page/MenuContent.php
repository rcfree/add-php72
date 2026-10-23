<?php

class ADD_Page_MenuContent extends ADD_Page_AbstractContent {
	function isWebContent() {
		return false;
	}
	function createMenuPath() {
		return "client/menu-content";
	}

    function getPanelName() {
        return "menu_side";
    }
}
