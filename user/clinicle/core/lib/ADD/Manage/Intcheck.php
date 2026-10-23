<?php
/**
 * Description of ADD_Manage_IntCheck
 *
 * @author rcf8
 */



class ADD_Manage_Intcheck implements ADD_ManageInterface
{
    function getDescription() {
        return "Integrity check";
    }
    function getOptsList() {
        return "m::";
    }
    function manage($options) {
        $modName = $options['m'];
        
        $it = new ADD_ModuleBundle_IntegrityCheck($modName);

        $it->initModule();
        $it->checkDependencies();
        $it->checkPageCreationAndSubmit();
        $it->checkBaseContent();
        $it->checkAfterSubmit();
        $it->checkOutput();
        $it->checkReports();
    }
    
    
}
