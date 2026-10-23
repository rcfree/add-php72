<?php
/**
 * Description of Load
 *
 * @author rcf8
 */


namespace ClinicLE\Manage\Command;

use ClinicLE\Command\AbstractCommand;
use ClinicLE\Common\Config\AbstractConfig;

class SetupCommand extends AbstractCommand
{
    function getOptsList() {
        return "f";
    }
    function getDescription() {
        return "Setup ADD";
    }
    function runCommand(AbstractConfig $options)
    {

$log = ADD::log();
        $db = ADD::db();
        $add = ADD::sys();
        $config = $add->assertConfig('Database');
        
        $insVer = isset($options['v']) ? $options['v'] : $add->getInstalledVersion();
        
        $insVer = str_replace(".","_",$insVer);
        
        $insFileName = $add->getPath()."/core/sql/setup/add_".$insVer.".sql";
        if (!file_exists($insFileName)) {
            throw new ADD_SystemException("Unable to find setup file $insFileName");
        }
			
        if(!isset($options['f'])) {
            echo "This WILL delete everything in an existing ADD database (".$config['dsn']."). Are you sure (Y/n)?\n";
            $value = ADD_Util::inputLine(1);
            if ($value!="Y") throw new Exception("User cancelled operation");
        }
        
        $workPath = $add->getPath()."/".$add->config('module_path');
        system("rm -rf $workPath/*");
        $log->info("Removing custom modules from $workPath");
        
        $log->info("Running setup file '$insFileName'");
        
        $db->executeFile($insFileName);
        
        $load = new ADD_Manage_Load();
        $load->manage(array("m"=>"patient","f"=>1));
		$load->manage(array("m"=>"client","f"=>1));
    }
}
