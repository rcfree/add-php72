<?php
/**
 * Description of ADD_Manage_IntCheck
 *
 * @author rcf8
 */



class ADD_Manage_Summary implements ADD_ManageInterface
{
    function getDescription() {
        return "Summary of ADD system";
    }
    function getOptsList() {
        return "";
    }
    function manage($options) {
        $log = ADD::log();
		$add = ADD::sys();
        $db = ADD::db();
        $add->initModules();
        
		$insVer = $add->getInstalledVersion();
		$dbVer = $db->getVersion();
		if($insVer!=$dbVer) $this->printLn("** PLEASE NOTE YOUR DATABASE NEEDS UPGRADING **");
		$this->printDivider();
		
		$this->printLn("ADD System Information");
		$this->printDivider();
		$this->printLn("");
		$this->printLn(" - Core system is v".$insVer);
		$this->printLn(" - Core database is v".$dbVer);
		$this->printLn("");
		$this->printLn("Installed ADD modules");
		$this->printDivider();
		$this->printLn("");
		$this->printLn ("  * indicates core module, ! indicates module incompatible with core system");
		$this->printLn("");
		$this->printLn(sprintf("%-40s","  name (title)").sprintf("%-22s","version").sprintf("%-15s","requires").sprintf("%-15s","extends").sprintf("%-25s","depends on"));
		$this->printDivider("  ");
		$coreCount=0;
		$installCount=0;
		$incompCount=0;
		$modCount=0;
		foreach($add->getModules() as $modName => $module) {
			$moduleLine="  ";
			$requires = $module->getSetting('requires');
			if ($requires) {
				$vc = new ADD_Tool_VersionCheck($requires);
				if(!$vc->isCompatibleWith($insVer))  {
					$moduleLine.="!";
					$incompCount++;
				}
			}
			$moduleLine.=$module->getName()." (".$module->getSetting('title').")";
			if($module->isCore()) {
				$coreCount++;
				$moduleLine.="*";
			}
			else {
				$installCount++;
			}
			$moduleLine=sprintf("%-40s",$moduleLine);
			
			
			if ($module->isCore()) {
				$moduleLine.=sprintf("%-22s","same as core");
			}
			else {
				list($version,$date) = $module->version();
				$moduleLine.=sprintf("%-20s",$version." ($date)");
			}
			$moduleLine.=sprintf("%-15s",$module->getSetting('requires'));
			$moduleLine.=sprintf("%-15s",$module->getSetting('extends'));
			$moduleLine.=sprintf("%-25s",$module->getSetting('uses'));
			//$moduleLine.="Requires:".$module->getSetting('requires','Any');
			$this->printLn($moduleLine);
			$modCount++;
		}
		$this->printLn("");
		$this->printLn("$modCount module(s). $coreCount ".ADD_Util::pluralize($coreCount,"core module").", $installCount ".ADD_Util::pluralize($installCount,"installed module").".");
		if ($incompCount>0) {
			$this->printLn("NOTE: $incompCount ".ADD_Util::pluralize($incompCount,"incompatible module"));
		}
		$this->printLn("");
    }
	private $_lastLine="";
	function printLn($line) {
		print $line."\n";
		$this->_lastLine = $line;
	}
	
	function printDivider($prefix="") {
		print $prefix.str_repeat("-",strlen($this->_lastLine))."\n";
	}
}
