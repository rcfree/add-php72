<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ADD_Manage_Upgrade implements ADD_ManageInterface
{
    function getOptsList() {
        return "";
    }
    function getDescription() {
        return "Upgrade ADD";
    }
    function manage($options) {
        $log = ADD::log();
		$add = ADD::sys();
        $db = ADD::db();
        //$add->initModules();
        
		$insVer = $add->getInstalledVersion();
        $curVer = $db->getVersion();
        
        $insVer = str_replace(".","_",$insVer);
        $curVer = str_replace(".","_",$curVer);
		
		$log->info("Upgrading ADD database structure from ".$curVer." to ".$insVer);
        
        $upgFileName = $add->getPath()."/core/sql/upgrade/add_".$curVer."-".$insVer.".sql";
		$force = isset($options['f']);
        //$modList=$add->getModules()->asArray();
		
		$db->beginTxn();
		
		$this->filesystem = new Filesystem();

		try{
			$this->upgradeDatabase($upgFileName,$force);
			if(!$force) $this->retestModules();
		}
		catch(Exception $ex) {
			$db->rollbackTxn();
			throw $ex;
		}
		$db->commitTxn();
    }
	function upgradeDatabase($upgFileName,$force) {
		$add = ADD::sys();
        $log = ADD::log();
		
		$db = ADD::db();
		if ($add->isCorrectVersion()) {
            $log->info("This ADD database is the correct version (".$add->getInstalledVersion().") the database does not need upgrading");
            return;
        }
		
        
		if (!file_exists($upgFileName)) {
            $log->info("Upgrade file ($upgFileName) not found so just updating version in DB");
            $db->execute("update version set version=?",$add->getInstalledVersion());
            return;
        }  
		$log->info("Running upgrade file '$upgFileName'");
        
        if(!$force) {
            echo "This WILL upgrade the existing ADD database (".$add->getConfig()->getSection('Database')->getSetting('dsn')."). Are you sure (Y/n)?\n";
            $value = ADD_Util::inputLine();
            if ($value!="Y") throw new Exception("User cancelled operation");
        }
		$db->executeFile($upgFileName);
		$db->execute("update version set version=?",$add->getInstalledVersion());
	}
	function retestModules() {
		$log = ADD::log();
        ADD_Tool_Command::executeWithException("echo");
		$finder = new Finder();
		$workModPath = ADD::sys()->getWorkModulePath();
		if($this->filesystem->exists($workModPath)) {
			$finder->in($workModPath)->depth("<2")->directories();
			foreach($finder as $dir) {
				$dirPath = $dir->getRealpath();
				if($this->hasDirectoryChanged($dirPath)) {
					throw new Exception("Changes have been made to files in $specificModPath");
				}
			}
			$log->info("No installed modules have been locally changed");	
			$this->filesystem->remove($workModPath);
		}
		
		$this->filesystem->remove(ADD::sys()->getPath()."/vendor/clinicle");
		$this->filesystem->remove(ADD::sys()->getPath()."/vendor/bin");
		
		$lockFile = ADD::sys()->getPath()."/composer.lock";
		if($this->filesystem->exists($lockFile)) {
			$this->filesystem->remove($lockFile);
		}
		ADD_Tool_Command::executeWithException("composer install");
	}
	
	private function hasDirectoryChanged($directory) {
		if(is_dir("$directory/.hg")) {
			exec('hg stat '.$directory, $output);
			$hasChanges = trim(implode("",$output)) ? true : false;
			return $hasChanges;
		}
		return false;
	}
}
