<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

class ADD_Manage_Load extends ADD_Manage_Install 
{
    function getOptsList() {
        return "m::fn";
    }
    function getDescription() {
        return "Load module (deprecated)";
    }
    function manage($options) {
		ADD::log()->warn("*** DEPRECATED *** the 'load' action is deprecated. Use 'install' instead");
		parent::manage($options);
	}
}
