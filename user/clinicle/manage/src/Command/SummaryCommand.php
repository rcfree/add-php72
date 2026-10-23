<?php
/**
 * Description of ADD_Manage_IntCheck
 *
 * @author rcf8
 */

namespace ClinicLE\Manage\Command;
use ClinicLE\Command\AbstractCommand;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Util;

use CLE;

class SummaryCommand extends AbstractCommand
{
    function getDescription() {
        return "Summary of ADD system";
    }
    function getOptsList() {
        return "";
    }
    function runCommand(AbstractConfig $options) {
        $log = CLE::log();
		$add = CLE::sys();
        $db = CLE::db();
        $add->initModules();
        
		$insVer = $add->getInstalledVersion();
		$dbVer = $db->getVersion();
		if($insVer!=$dbVer) $this->printLn("** PLEASE NOTE YOUR DATABASE NEEDS UPGRADING **");
		$this->printDivider();
		
		$this->printLn("ADD System Information");
		$this->printDivider();
		$this->printLn("");
		$this->printLn(" - Core system is ".$insVer);
		$this->printLn(" - Core database is v".$dbVer);
		$this->printLn("");
		$this->printLn("Installed ADD modules");
		$this->printDivider();
		$this->printLn("");
        if($options->has("n")) {
            $this->printLn("** Only showing modules with new versions");
        }
		$this->printLn("");
		$this->printLn(sprintf("%-40s","  name (title)").sprintf("%-11s","tag").sprintf("%-11s","branch")."new_version");

		$this->printDivider("  ");
		$coreCount=0;
		$installCount=0;
		$incompCount=0;
		$modCount=0;
		foreach($add->getModules() as $modName => $module) {
			$moduleLine="  ";

			$moduleLine.=$module->getName()." (".$module->getSetting('title').")";

			$moduleLine=sprintf("%-40s",$moduleLine);
			
			
			if ($module->isCore()) {
				$moduleLine.=sprintf("%-22s","same as core");
			}
			else {
				list($tag,$branch) = $module->version();
				$moduleLine.=sprintf("%-10s","$tag")." ";
                $moduleLine.=sprintf("%-10s","$branch");
			}

			$tag=str_replace("v","",$tag);
            $branch = str_replace("v","",$branch);
			$tagBits = explode(".",$tag);
            $branchBits = explode(".",$branch);

            if($tagBits[0]!=$branchBits[0] || $tagBits[1]!=$branchBits[1]) {
                $newVersion ="*";
            }
            else {
                $newVersion = " ";
            }
            $moduleLine.=" ".$newVersion;

            if($options->has("n") && $newVersion!="*") {
                continue;
            }
            $this->printLn($moduleLine);

            $modCount++;
		}
		$this->printLn("");
		$this->printLn("$modCount module(s). $coreCount ".Util::pluralize($coreCount,"core module").", $installCount ".Util::pluralize($installCount,"installed module").".");
		if ($incompCount>0) {
			$this->printLn("NOTE: $incompCount ".Util::pluralize($incompCount,"incompatible module"));
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
