<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Manage\Runner;
use Composer\Autoload\ClassLoader;

class RunnerEnvironment {
	public function __construct($path, $profile, $cmdOptions, ClassLoader $classLoader) {
		if (!$path) {
			$path = getcwd();
			print "WARNING: No ADD_PATH env variable set, using current path ($path)\n";
		}

		if (!$profile) {
			die("ERROR: No ADD_PROFILE env variable set");
		}

		if (!file_exists($path . "/vendor/autoload.php")) {
			die("ERROR: Composer vendor/autoload.php missing from $path");
		}
		$this->path = $path;
		$this->profile = $profile;

		list($this->options, $this->manageAction) = $this->resolveArguments($cmdOptions);
		//if(!$this->manageAction) throw new Exception("No command provided");
		$this->classLoader = $classLoader;
	}
	
	public function replaceClassMap($sourcePath, $targetPath) {
		$classMap = $autoload->getClassMap();
	
		$replaceClassMap = array();
		
		foreach($classMap as $className => $classFile) {
			$replaceClassFile=str_replace($sourcePath,$targetPath,$classFile);
			$replaceClassMap[$className]=$replaceClassFile;
		}
		$autoload->addClassMap($replaceClassMap);
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getProfile() {
		return $this->profile;
	}
	
	public function getManageAction() {
		return $this->manageAction;
	}
	public function getClassLoader() {
		return $this->classLoader;
	}
	
	function resolveArguments(array $cmdOptions) {
		$options = array();
		$script = array_shift($cmdOptions);
		foreach ($cmdOptions as $cmdOption) {
			$matches = array();

			if (preg_match("/^\-([A-Za-z])(.*)$/", $cmdOption, $matches)) {
				$options[$matches[1]] = $matches[2];
			} else {
				$options['m'] = $cmdOption;
			}
		}
		$manageAction = array_shift($cmdOptions);

		return array($options, $manageAction);
	}

}
