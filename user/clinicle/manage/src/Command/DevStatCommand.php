<?php
/**
 * Description of Load
 *
 * @author rcf8
 */


namespace ClinicLE\Manage\Command;

use ClinicLE\Command\AbstractCommand;
use ClinicLE\Install\ModuleInstall;
use ClinicLE\System\SystemCreator\ApplicationSystemCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Common\Config\AbstractConfig;
use ADD;
use ClinicLE\Module\SeparateModuleSetup;
class DevStatCommand extends Command
{
	private $checkPaths = array("clinicle","add","cdbrkr");
    function getOptsList() {
        return "";
    }
    function getDescription() {
        return "Check development (e.g. changed) status of modules and libraries";
    }

    public function configure() {
        $this
            ->setName("dev-stat")
            ->setDescription("Development status")
            ->setHelp(<<<EOT
The <info>dev-stat</info> command determines if any of the libraries and modules in the clinicle system have been changed since they have been installed. Currently it only supports mercurial (hg stat).

Usage:
<info>clinicle dev-stat</info>

EOT
            );
    }
    public function execute(InputInterface $input, OutputInterface $output) {
        $creator = new ApplicationSystemCreator();
        $add = $creator->create();

		foreach($add->getModules() as $module) {
			$modName = $module->getName();
			$liveModPath = $module->getPath();
			
			$sourceModPath = $add->getSourceModulePath($modName);
			$this->checkLibrary($modName,$sourceModPath,$liveModPath);
		}
		
		$finder = $add->getService(CoreServices::FILEFINDER);
		$finder
		->directories()
		->depth('1')
		->in($add->getPath()."/vendor");
		
		foreach($finder as $file) {
			if(!in_array($file->getRelativePath(),$this->checkPaths)) {
				continue;
			}
			$libName = $file->getRelativePathname();
			$liveLibPath = ADD::sys()->getPath()."/vendor/".$libName;
			$this->checkLibrary($libName,null,$liveLibPath,"library");
		}
    }
	private function checkLibrary($modName,$sourceModPath=null,$liveModPath,$libType="module") {
		$log = ADD::log($this);
		
		$liveIsSymlink = is_link($liveModPath);
			
		if($liveIsSymlink && $sourceModPath) {
			if (readlink($liveModPath)!=$sourceModPath) {
				$log->warn("Live $libType '".$modName."' is not symlinked to normal source (".readlink($liveModPath).")");
			}
			
			$sourceChanged = $this->hasDirectoryChanged($sourceModPath);
			if($sourceChanged) {
				$log->warn("SOURCE $libType '".$modName."' changed (".$sourceModPath.") (s/l live)");
			}
			return;
		}
		
		if($sourceModPath) {
			$sourceChanged = $this->hasDirectoryChanged($sourceModPath);
			if($sourceChanged) {
				$log->warn("SOURCE $libType '".$modName."' changed (".$sourceModPath.")");
			}
		}
		$liveChanged = $this->hasDirectoryChanged($liveModPath);
		
		if($liveChanged) {
			$log->warn("LIVE $libType '".$modName."' changed (".$liveModPath.")");
		}
		
	}
	private function hasDirectoryChanged($directory) {
		if(is_dir("$directory/.hg")) {
			$cwd = getcwd();
			chdir($directory);
			exec('hg stat | grep -v vendor | grep -v modules | grep -v ".orig"', $output);
			chdir($cwd);
			$hasChanges = trim(implode("",$output)) ? true : false;
			return $hasChanges;
		}
		return false;
	}
	
	
}
