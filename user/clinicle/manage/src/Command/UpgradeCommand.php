<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

namespace ClinicLE\Manage\Command;

use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Util;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\System\Service\Process;
use DateTime;
use Exception;
use CLE;
use Symfony\Component\Filesystem\Filesystem;

class UpgradeCommand extends \ClinicLE\Command\AbstractCommand
{
    function getOptsList() {
        return "v";
    }
    function getDescription() {
        return "Upgrade system";
    }

    function runCommand(AbstractConfig $options) {
    	$add = CLE::sys();
    	$repo = $add->getConfig()->assert("repository");
		$log = CLE::log($this);
		$version = $options->assert("v");
		$upgradeBase = $add->getPath()."/tmp/upgrade";
		$upgradeDir = time();
		$upgradePath = $upgradeBase."/".time();
        if(!is_dir($upgradeBase)) {
            mkdir($upgradeBase);
        }
        chdir($upgradeBase);
		$this->system("hg clone ".$repo." ".$upgradeDir." -r".$version);
        $this->tryRunningUpgradeFile($version,$upgradePath);
	}
	function tryRunningUpgradeFile($newVersion,$upgradePath) {
        $currentVersion = CLE::sys()->getInstalledVersion();
        $upgradeFile = $currentVersion."_".$newVersion.".php";
        $upgradeFilePath = $upgradePath."/upgrade/".$upgradeFile;

        if(!file_exists($upgradeFilePath)) {
            CLE::warn("No upgrade file found $upgradeFilePath");
            return;
        }

        CLE::info("Running upgrade file $upgradeFilePath");
        include_once($upgradeFilePath);
    }
	function system($cmd) {
        CLE::info("cmd:$cmd");
        system($cmd);
    }
	static function getPassword($stars = false)
	{
	    // Get current style
	    $oldStyle = shell_exec('stty -g');
	
	    if ($stars === false) {
	        shell_exec('stty -echo');
	        $password = rtrim(fgets(STDIN), "\n");
	    } else {
	        shell_exec('stty -icanon -echo min 1 time 0');
	
	        $password = '';
	        while (true) {
	            $char = fgetc(STDIN);
	
	            if ($char === "\n") {
	                break;
	            } else if (ord($char) === 127) {
	                if (strlen($password) > 0) {
	                    fwrite(STDOUT, "\x08 \x08");
	                    $password = substr($password, 0, -1);
	                }
	            } else {
	                fwrite(STDOUT, "*");
	                $password .= $char;
	            }
	        }
	    }
	
	    // Reset old style
	    shell_exec('stty ' . $oldStyle);
	
	    // Return the password
	    return $password;
	}

}
?>