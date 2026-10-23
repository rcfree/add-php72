<?php

class ADD_PathologyFactory_Haem extends ADD_PathologyFactory_Base {
	function getEncType() {
		return "path-tests-haemresult";
	}
	
	function getSummaryType() {
		return "path-tests-haem";
	}
	
	function getTestType() {
		return "Haem";
	}
	
}