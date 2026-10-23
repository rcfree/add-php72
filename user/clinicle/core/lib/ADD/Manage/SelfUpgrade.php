<?php
/**
 * Description of Load
 *
 * @author rcf8
 */
	



error_reporting(E_ERROR | E_WARNING);
ini_set("track_errors",0);

class ADD_Manage_SelfUpgrade implements ADD_ManageInterface
{
    function getOptsList() {
        return "taflnc";
    }
    function getDescription() {
        return "Self-upgrade ADD";
    }
	function getUpgradePath($addPath,$upgradeDir) {
		$upgradePath = $addPath."/".$upgradeDir;
		return $upgradePath;
	}
	function _createUpgradePath() {
		if(file_exists($this->_upgradePath)) {
			system("rm -rf {$this->_upgradePath}");
		}
		mkdir($this->_upgradePath);
		
		return $this->_upgradePath;
	}
	function _cloneADDCore() {
		//clone ADD core
		$lrbPath = ADD::sys()->assertConfig("lrb_path");
		
		ADD::log()->info("Clone latest version of ADD from repo");
		$this->_removeUpgradePathIfExists("core");
		chdir($this->_upgradePath);
		system("hg clone https://bitbucket.org/leicrespbru/add core -rdefault");
		//system("cp -r ".$this->_addPath."/core ".$this->_upgradePath."/");
		$this->_createSymlinkToLrbtools($this->_upgradePath);
	}
	
	function _createSymlinkToLrbtools($basePath) {
		$lrbPath = ADD::sys()->assertConfig("lrb_path");
		
		if(!file_exists($lrbPath)) throw new Exception("LRB path $lrbPath does not exist");
		if(file_exists($basePath."/core/lib/LRB")) return;
		
		ADD_Tool_Command::executeWithException("ln -s ".$lrbPath."/lib/LRB ".$basePath."/core/lib/.");
		
		
	}
	function _copyConfigToUpgradePath($addProfile,$addConfigPath) {
		//link to config files
		$upgradeConfigPath = $this->_upgradePath."/config";
		ADD::log()->info("Copy config profile '".$addProfile." from '$addConfigPath' to '$upgradeConfigPath'");
		$this->_removeUpgradePathIfExists("config");
		mkdir($upgradeConfigPath);
		system("cp -r ".$addConfigPath." $upgradeConfigPath");
		return $upgradeConfigPath."/".$addProfile;
	}
	
	function _copyWorkToUpgradePath($workPath,$fromLive=false) {
		$this->_removeUpgradePathIfExists("work");
				
		//copy current work directory to upgrade path
		if($fromLive) {
			ADD::log()->info("Secure copy existing modules from live server to upgrade path");
			system("scp -r lrbaddlive.lrbru.xuhl-tr.nhs.uk:/local/add/work ".$this->_upgradePath."/");
		}
		else {
			ADD::log()->info("Copy existing modules to upgrade path");
			
			system("cp -r ".$workPath." ".$this->_upgradePath."/");
		}
		
	}
	function _removeUpgradePathIfExists($file) {
		if(file_exists($this->_upgradePath."/".$file)) {
			system("rm -rf ".$this->_upgradePath."/".$file);
		}
	}
	function _copyVendorToUpgradePath($vendorPath) {
		//copy current work directory to upgrade path
		ADD::log()->info("Symlink vendor files to upgrade path");
		$this->_removeUpgradePathIfExists("vendor");
		system("cp -r $vendorPath ".$this->_upgradePath."/");
	}
	
	function _makeSourceModuleDir($sourceModuleDir) {
		$sourceModulePath = $this->_upgradePath."/".$sourceModuleDir;
		//copy current work directory to upgrade path
		ADD::log()->info("Create source module path '$sourceModulePath'");
		$this->_removeUpgradePathIfExists($sourceModuleDir);
		mkdir($sourceModulePath);
	}
	
	function _modifyUpgradeConfig($fileName) {
		$config = parse_ini_file($fileName,true);
		$config['source_module_path']=$this->_upgradePath."/src_modules";
		$this->_writePhpIni($config,$fileName);
	}

	function _writePhpIni($array, $file)
	{
		$res = array();
		foreach($array as $key => $val)
		{
			if(is_array($val))
			{
				$res[] = "[$key]";
				foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
			}
			else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
		}
		file_put_contents($file, implode("\r\n", $res));
	}
	function _updateVendorDeps() {
		chdir($this->_upgradePath);
		system("curl -sS https://getcomposer.org/installer | php");
		system("php composer.phar -d=core update");
	}
    function manage($options) {
        $log = ADD::log();
		$add = ADD::sys();
        $db = ADD::db();
		$this->_options=$options;
		$this->_addPath=getenv('ADD_PATH');
		
		$this->_upgradePath = $this->getUpgradePath(getenv('ADD_PATH'),"self-upgrade");
		
		$this->_createUpgradePath();
		$this->_cloneADDCore();
		
		$configPath = $this->_copyConfigToUpgradePath($add->getProfile(),$add->configPath());
		
		$this->_copyWorkToUpgradePath(ADD::sys()->getWorkModulePath(),isset($options['c']));
		$this->_copyVendorToUpgradePath(getenv('ADD_PATH')."/vendor");
		$this->_updateVendorDeps();
		
		$this->_makeSourceModuleDir("src_modules");
		$this->_modifyUpgradeConfig($configPath."/add.ini");
		
		putenv("ADD_PATH=".$this->_upgradePath);
		$add->setPath($this->_upgradePath);
		
		$init = new ADD_Initialiser_Standard($this->_upgradePath,$add->getProfile());
		$add = ADD::init($init);
		
		try {
			$add->initModules();
		}
     	catch(ADD_FatalErrorException $ex) {
			$log->error("Error initialising existing modules: ".$ex->getMessage());
		}
        catch(Exception $ex) {
			$log->error("Error initialising existing modules: ".$ex->getMessage());
		}

		$testSingleModule = ADD_Util::val('t',$options);
		$autoLoad = isset($options['a']);
		$failedModuleNames=array();
		
		if($testSingleModule) {
			$moduleList=array($testSingleModule=>$add->getModule($testSingleModule));
		}
		else {
			$moduleList=$add->getModules()->asArray();
		}
		$log->info("Existing modules to test:".implode(",",array_keys($moduleList)));
		
		foreach($moduleList as $name => $module) {
			try {
				$this->_retestModule($name);	
			}
			catch(Exception $ex) {
				$this->_dealWithTestException($name,$autoLoad,$ex);
				$failedModuleNames[]=$name;
			}
		}
		if(count($failedModuleNames)>0 && $autoLoad) {
			$this->_attemptModuleDownloads($failedModuleNames,isset($options['l']));	
		}
		
		if($testSingleModule) {
			$log->info("Only single module tested. Not replacing ADD system");
			return;
		}
		
		if(isset($options['n'])) {
			$log->info("Dry run only. Not replacing with new version of ADD or modules");
			return;
		}
		$this->_replaceWithNewVersion();
		$this->_upgradeFailedModules($failedModuleNames);
		$this->_upgradeSymLinks($moduleList,$failedModuleNames);
		
		$manageUpgrade = new ADD_Manage_Upgrade();
		$manageUpgrade->manage(array("f"=>1));
    }
	
	function _replaceWithNewVersion() {
		$upgradeBackupDir = $this->_addPath."/upgrade-".date("dmYHis");
		ADD::log()->info("Move old system from ".$this->_addPath." into '$upgradeBackupDir'");
		if(!file_exists($upgradeBackupDir)) {
			mkdir($upgradeBackupDir,0755,true);
		}
		
		ADD::log()->info("Move new system from ".$this->_upgradePath." into '".$this->_addPath."'");
		
		system("mv ".$this->_addPath."/core ".$upgradeBackupDir."/");
		system("mv ".$this->_addPath."/vendor ".$upgradeBackupDir."/");
		system("mv ".$this->_upgradePath."/core ".$this->_addPath);
		system("mv ".$this->_upgradePath."/vendor ".$this->_addPath);
		
		putenv("ADD_PATH=".$this->_addPath);
		$this->_createSymlinkToLrbtools($this->_addPath);
		
		chdir($this->_addPath);
		$moduleViewPath = $this->_addPath."/core/web/application/views/module";
		if(!file_exists($moduleViewPath)) {
			mkdir($moduleViewPath);
		}
		$moduleWebPath = $this->_addPath."/core/web/module";
		if(!file_exists($moduleWebPath)) {
			mkdir($moduleWebPath);
		}
		
		
		$init = new ADD_Initialiser_Standard($this->_addPath,ADD::sys()->getProfile());
		$add = ADD::init($init);
		
		$add->initModules();
		
	}
	
	function _upgradeFailedModules($failedModuleNames) {
		foreach($failedModuleNames as $failedModuleName) {
			ADD_Command::executeWithException(ADD::sys()->getPath()."/core/bin/add-manage install $failedModuleName -d");
		}
	}
	function _upgradeSymLinks($modules,$failedModuleNames) {
		foreach($modules as $module) {
			if(in_array($module->getName(),$failedModuleNames)) continue;
			ADD_Tool_Install::createModuleSymLinks($module->getName(),ADD::sys()->getPath()."/work");
		}
	}
	function _dealWithTestException($testSingleModule,$autoLoad, $testException) {
		$modText =is_a($testException,"ADD_CommandReturnValueException") ? "OUTPUT:".$testException->getOutputAsString() : "MESSAGE:".$testException->getMessage();
		$message = "An error occurred while testing existing module: $testSingleModule so the latest version will be downloaded";
		
		if(!$autoLoad) throw $testException;//new Exception($message);
		ADD::log()->error($message);
	}
	
	function _downloadAndRetestModule($modName) {
		$module = ADD::sys()->getModule($modName);		
		if($module && $module->isCore()) throw new Exception("Cannot re-download core module: $modName");
		list($beforeVersion,$beforeDate) = $module ? $module->version() : array("?.?.?","?/?/?");
		ADD::log()->warn("Errors initialising module: $modName $beforeVersion ($beforeDate) so attempting to download latest version");
	
		ADD_Tool_Install::downloadModule(array("d"=>1),$modName);
		$this->_retestModule($modName);
		
		$moduleSetup = new ADD_ModuleSetup(ADD::sys());
		$moduleSetup->loadSingleModuleWithDependencies($modName, $this->_upgradePath."/work", $this->_upgradePath."/work");
		$module = ADD::sys()->getModule($modName);
		list($afterVersion,$afterDate) = $module ? $module->version() : array("?.?.?","?/?/?");
		
		
		ADD::log()->info("Downloaded module: $modName $afterVersion ($afterDate) installed successfully");
	}
	
	function _attemptModuleDownloads($modNames, $listFailed=false) {
		$log = ADD::log();
		$command = new ADD_Tool_Command();
		$failedModDownloads=array();
		foreach($modNames as $modName) {
			try {
				$this->_downloadAndRetestModule($modName);	
				
			}
			catch(ADD_CommandReturnValueException $ex) {
				if($listFailed)	{
					$failedModDownloads[$modName]=$ex->getOutputAsString();
					continue;
				}
				else {
					throw new Exception("Downloaded module: $modName did not install sucessfully\n".$ex->getOutputAsString());
				}
			}
			catch(Exception $ex) {
				if($listFailed)	{
					$failedModDownloads[$modName]=$ex->getMessage();
					continue;
				}
				else {
					throw new Exception("Downloaded module: $modName did not install sucessfully\n".$ex->getMessage());
				}
			}
		}
		
		if(count($failedModDownloads)>0) {
			$errString = "Downloaded latest versions of modules failed tests:".implode(",",array_keys($failedModDownloads));
			$errLogFile = $this->_addPath."/log/self-upgrade-".date("dmYHis")."-error.log";
			$err = fopen($errLogFile,"w");
			foreach($failedModDownloads as $modName => $error) {
				fwrite($err,"MODULE ERRORS for:".$modName."\n\n");
				fwrite($err,$error."\n*********\n");
			}
			$log->error("Errors have been output to ".$errLogFile);
			throw new Exception($errString);
			
		}
			
	}
	function _retestModule($moduleName) {
		$log = ADD::log();
        
		$module = ADD::sys()->getModule($moduleName);
		putenv('ADD_MOD_PATH_TEST='.ADD::sys()->getSourceModulePath());
		
		if(!$module) {
			throw new ADD_ModuleDoesNotExistException("Error occurred while trying to initialise module '$moduleName'");
		}
		$log->info("Retest existing module ".$module->getName()." in path ".$module->getPath());
		$md = new ADD_Test_ModuleDependency($module,ADD::sys()->getModules());
		
		$md->assertCorrectVersion(ADD::sys()->getInstalledVersion(),"System");
		$md->assertModuleDependencies();

		$ut = new ADD_Test_ModuleTest($md,false,true);

		$ut->runTests(null,null,null,false);
	}
}
