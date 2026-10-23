<?php

/**
 * Loads a config based on a supplied base path and profile
 * Includes initialisation which autoloads composer dependencies and updating the PHP include path.
 * Also provides inherited methods to get common paths to modules and web application.
 *
 * User: rcf8
 * Date: 17/03/2015
 * Time: 14:34
 */
namespace ClinicLE\System\Environment;
use ClinicLE\Common\Exception\DirectoryNotFoundException;
use ClinicLE\Common\Config\FileConfig;

class StandardEnvironment extends AbstractEnvironment {
	const CONFIG_FILE="add.ini";
    /**
     * Initialises profile and config using core path and supplied profile
     *
     * @param $path
     * @param $profile
     * @throws ADD_Exception_DirectoryNotFoundException
     * @throws ADD_Exception_NullValueException
     */
    function __construct($path,$profile) {
		parent::__construct($path,$profile);

        if (!file_exists($path)) throw new DirectoryNotFoundException("System path '$path' does not exist");
        $configFile = $this->getConfigPath()."/".self::CONFIG_FILE;

        $conf = new FileConfig($configFile);
        $this->config = $conf;
    }

    /**
     * Initialise composer dependencies
     *
     * @throws FileNotFoundException
     */
	function initEnvironment() {
        $path = $this->_path;
        if (!file_exists($path."/vendor/autoload.php")) {
			throw new FileNotFoundException("Composer autoload.php not found in $path/vendor");
		}
        require_once($path."/vendor/autoload.php");
    }

}
