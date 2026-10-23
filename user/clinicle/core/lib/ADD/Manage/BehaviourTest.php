<?php
/**
 * Description of ADD_Manage_IntCheck
 *
 * @author rcf8
 */


class ADD_Manage_BehaviourTest implements ADD_ManageInterface
{
    function getDescription() {
        return "Behaviour test system";
    }
    function getOptsList() {
        return "";
    }
    function manage($options) {
    	chdir(getenv('ADD_PATH'));
        system("vendor/bin/behat -fprogress",$returnVar);
        if($returnVar!=0) exit($returnVar);
    }
}
