<?php

class ADD_PathologyFactory_Micro extends ADD_PathologyFactory_Base {
	function getEncType() {
		return "path-tests-microresult";
	}
	
	function getSummaryType() {
		return "path-tests-micro";
	}
	
	function getTestType() {
		return "Micro";
	}
	
}