<?php
class LRB_InitCivi {
	static function init($ctpath,$drupalpath) {	
		$civipath = "$drupalpath/sites/all/modules/civicrm";
		
		define("CIVICRM_CONFDIR","$drupalpath/sites/default");
		set_include_path($ctpath."/lib".":".$ctpath."/vendor/php".":"."$civipath/drupal" . PATH_SEPARATOR . $civipath . PATH_SEPARATOR . "$drupalpath/sites/default");
		date_default_timezone_set('UTC');
		
		require_once 'civicrm.config.php';
		$config =& CRM_Core_Config::singleton( );
		return $config;
	}
}
