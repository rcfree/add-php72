<?php
/**
 * Description of Load
 *
 * @author rcf8
 */
namespace ClinicLE\Install\Command;
use ClinicLE\Install\ModuleInstall;
use Composer\Autoload\ClassMapGenerator;
use Symfony\Component\Filesystem\Filesystem;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Module\ModuleSetup;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Command\AbstractCommand;
use ADD;

class InstallCommand extends AbstractCommand 
{
    function getOptsList() {
        return "m::fnduriop";
    }
    function getDescription() {
        return "Install module";
    }
	
    function _runTestsIfRequired($options,$modName) {
            $log = ADD::log();

            if (isset($options['f'])) { //force install
                    $log->warn("FORCE INSTALL module '".$modName."' (no tests run)");
                    return;
            }
            ADD::sys()->executeManageTool("test",$modName);
    }
    
    function runCommand(AbstractConfig $options) {
        $returnVar = 0;
        $log = ADD::log($this);
        
        $add = ADD::sys();
		
        $modName = $options->get("m") or die("FATAL: Module (-m) not specified\n");
		$modVersion = $options->get("v");
		$inline = $options->has("i");
		$download = $options->has("d");
		$force = $options->has("f");
		$deleteMissing = $options->has("p");
		$this->edb = ADD::db('encounter');
        $this->pdb = ADD::db('patient');
        $this->ddb = ADD::db('dictionary');

        $this->filesystem = ADD::sys()->getService(CoreServices::FILESYSTEM);

        chdir($add->getPath());

        
        $modPath = $add->getWorkModulePath($modName);
        $vendorPath = $add->getPath()."/vendor/$modName";
		
        $required=true;
        $vendor = false;
        $systemMod=false;
		
        if(file_exists($modPath)) {
                $log->info("Removed existing live module at:$modPath");
				$this->backupAndRemovePath($add->getWorkModulePath(),$modName,$force);
			
                $required=false;
        }
        
        if(file_exists($vendorPath)) {
			
            $log->info($modName." is a vendor module ($vendorPath). Are you sure? Y/n");
            $value = ADD_Util::inputLine(1);
            if($value=="n") {
                    return;
            }
            if($vendorPath=="clinicle/system") {
                $log->info("clinicle/system module not removed until later");
            
                $systemMod=true;
            }
            if(!$systemMod) {
                $this->backupAndRemovePath($add->getPath()."/vendor",$modName,$force);
            }
            $required=false;
            $vendor=true;
        }
      
		$sourceModulePath = $add->getSourceModulePath($modName);
		$targetSymLink = $vendor ? $add->getPath()."/vendor/".$modName : $add->getWorkModulePath($modName);
			
        if($download) {
			if($systemMod) {
				$this->filesystem->remove($vendorPath);
			}
			if($required) {
				if(!$modVersion) {
					throw new \InvalidArgumentException("No required module version was provided (e.g. ".$modName." -v1.0
					.x-dev)");
				}
				ADD::sys()->getService(CoreServices::PROCESS)->executeWithException("composer require ".$modName."=".$modVersion." --no-update",true);
			}
			$this->backupAndRemovePath($add->getSourceModulePath(),$modName,$force);
			
			system("composer update -vv");
			
			
			if($inline) {
				ADD::log($this)->info("Moving module definition to $sourceModulePath and creating symlink to $targetSymLink");
				$this->filesystem->mirror($targetSymLink,$sourceModulePath);
				$this->filesystem->remove($targetSymLink);
				$this->filesystem->symlink($sourceModulePath,$targetSymLink);
			}
			/*
			else {
				ADD::log($this)->info("Copy module definition $targetSymLink to $sourceModulePath");

				$this->filesystem->mirror($targetSymLink,$sourceModulePath);
			}
			*/
		}
		else {
			$srcModulePath = $add->getSourceModulePath();
			$depModPath = $add->getWorkModulePath();

			$environment = $this->getRunner()->getEnvironment();
			$classLoader = $environment->getClassLoader();
			$classMap = ClassMapGenerator::createMap($sourceModulePath);

			$classLoader->addClassMap($classMap);

			$ms = new \ClinicLE\Module\SingleModuleSetup(ADD::sys());
			$ms->setSingleModuleName($modName);
			$ms->setSingleModulePath($srcModulePath);
			$ms->setIgnoreSubModule(true);
			$ms->loadModules();
			
			$events = ADD::sys()->getEventDispatcher();
            $events->dispatch(\ClinicLE\Install\Event\InstallEvents::BEFORE_RUN);
		
			$module = ADD::sys()->getModules()->get($modName);
			
			$moduleInstall = new ModuleInstall();
			$moduleInstall->installForms($module, $deleteMissing);

			if($systemMod) {
				$this->filesystem->remove($vendorPath);
			}
			
			if($inline) {
				ADD::log($this)->info("Link inline module $sourceModulePath to $targetSymLink");
				$this->filesystem->symlink($sourceModulePath,$targetSymLink);
			}
			else {
				ADD::log($this)->info("Copy module definition $sourceModulePath to $targetSymLink");

				$this->filesystem->mirror($sourceModulePath,$targetSymLink);
			}
		}
        
        ADD::sys()->getService(CoreServices::PROCESS)->executeWithException("composer dump-autoload --optimize");

    }
	private function hasDirectoryChanged($directory) {
		if(is_dir("$directory/.hg")) {
			exec('hg stat '.$directory, $output);
			$hasChanges = trim(implode("",$output)) ? true : false;
			return $hasChanges;
		}
		return false;
	}
	
	private function backupAndRemovePath($modPath, $modName, $force=false) {
		 $log = ADD::log($this);
        $specificModPath = $modPath."/".$modName;
		if (!file_exists($specificModPath)) return;
		if(is_link($specificModPath)) {
			$log->info("Module path '$specificModPath' is symlink");
			return;
		}
		else {
			if(!$force && $this->hasDirectoryChanged($specificModPath)) {
				throw new Exception("Changes have been made to files in $specificModPath");
			}
		
		
			$prevPath = $modPath."/../backup";
			if(!file_exists($prevPath)) {
				mkdir($prevPath);
			}
			$timestampDir = $prevPath."/".$modName."-".date("dmYHis");
			$log->info("Moving existing '".$modName."' module from '$specificModPath' into '$timestampDir'");
			$this->filesystem->mirror($specificModPath,$timestampDir);
		}
		$this->filesystem->remove($specificModPath);	
	}

}
