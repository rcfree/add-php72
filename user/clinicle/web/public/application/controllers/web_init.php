<?php 

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$home =getenv('ADD_PATH');
$profile = getenv('ADD_PROFILE');
$errorPage = $home."/vendor/clinicle/web/public/application/views/error.php";

try {

include_once($home."/vendor/autoload.php");
$environment = new \ClinicLE\System\Environment\StandardEnvironment($home,$profile);
	$init = new \ClinicLE\System\Initialiser($environment);
	$init->setErrorsHandled(true);
    $system = ADD::init($init);

    $init->initSystem($system);  
	  
}
catch(Exception $ex) {
    $error = $ex->getMessage()."<pre>".$ex->getTraceAsString()."</pre>";
    include_once($errorPage);
    exit;	
}

function init_add_from_ci($ci) {
	$add = ADD::sys();

	
	$apiUser = true;
	if (method_exists($ci,"getApiUser")) {
		if ($ci->getApiUser()) {
			$user = $ci->getApiUser();
			$apiUser = true;
		}
		else {
			$user = $ci->ion_auth->user()->row();
		}
	}
	else {
		$user = $ci->ion_auth->user()->row();
	}
	
	$am = $add->getAccessManager();
	$am->setSessionData($ci->session->all_userdata());
	$am->setContext($ci);
	
	if(!$user) return;
	
	$am->populateUserGroups($ci->ion_auth->get_users_groups($user->id)->result(), $ci->ion_auth->is_admin($user->id));
	$am->setUser($user);
	
	$am->setLoggedIn($apiUser ? true : $ci->ion_auth->logged_in());

}

