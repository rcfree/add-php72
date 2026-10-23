<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Environment;
use Composer\Autoload\ClassLoader;

class CommandEnvironment extends StandardEnvironment {
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
		parent::__construct($path,$profile);

		list($this->options, $this->manageAction) = $this->resolveArguments($cmdOptions);
		//if(!$this->manageAction) throw new Exception("No command provided");
		$this->classLoader = $classLoader;
	}
	
	public function getOptions() {
		return $this->options;
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
    function useCleanRegistry() {
        return true;
    }
}
