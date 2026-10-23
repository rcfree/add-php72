<?php
/**
 * The System class is a singleton which acts as a registry for ADD.
 * 
 * The System class requires a path and profile to initialise. It then loads the configuration files for that profile.
 * Then it provides access to all Module classes loaded into the ADD framework.
 * And access to the manager classes including AccessManager and EncounterManager.
 *
 * @package Main
 * @example $sys = new ADD_System("/local/add","add-test");
 * @version 2.00
 * @since 14/03/2014
 * @author Rob Free
 */

class ADD_System {
	const READONLY="readOnly";
	const AUTOREFRESH="autorefresh";
	const INUSE="inUse";
	const NO_UNCLEAN_WARNING="NO_UNCLEAN_WARNING";
	const EVAL_EXCEPTION="EVAL_EXCEPTION";
}
?>