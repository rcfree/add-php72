<?php

/**
 * Abstract Profile class which does not access resources (e.g. config files)
 * Provides methods to get common paths to modules and web application.
 *
 * User: rcf8
 * Date: 17/03/2015
 * Time: 14:34
 */
namespace ClinicLE\System\Environment;
use ClinicLE\Common\Exception\FileNotFoundException;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\System\Exception\ExecuteFailedException;

class AbstractEnvironment {
    protected $path;
    protected $profile;
    protected $config;
	protected $version;
	protected $cleanRegistry = false;

    const PROFILE_DIR="config";

    /**
     * Initialises profile and config using core path and supplied profile
     *
     * @param $path
     * @param $profile
     * @throws DirectoryNotFoundException
     * @throws NullValueException
     */
    function __construct($path,$profile) {
        //set the home path (if none is provided use the env variable 'ADD_PATH')
        if (!$path || strlen($path)==0) {
            throw new NullValueException("No path provided to Environment");
        }

        //set the home profile, if none is provided use the env variable 'ADD_PROFILE' or if that is not available
        if (!$profile) {
            throw new NullValueException("No profile provided to Environment");
        }

        $this->path = $path;
        $this->profile =$profile;
    }
	
	/**
	 * 
	 */
	function initEnvironment() {
	}
    /**
     * Get the profile name
     *
     * @return mixed
     */
    function getProfile() {
        return $this->profile;
    }

    /**
     * Get the core path
     *
     * @return mixed
     */
    function getPath() {
        return $this->path;
    }

    /**
     * Get the config related to the profile
     *
     * @return AbstractConfig
     */
    function getConfig() {
        return $this->config;
    }

    /**
     * Set the config related to the profile
     *
     * @param AbstractConfig $config
     */
    function setConfig(AbstractConfig $config) {
        $this->config = $config;
    }

    /**
     * Get path to current profile directory (e.g. /local/add/config/test-site)
     * @return string
     */
    function getConfigPath() {
        $confProfile=$this->getProfile();;
        $confPath=$this->getPath()."/".self::PROFILE_DIR."/".$confProfile;
        return $confPath;
    }

    /**
     * Get the source module path, or that of the module if a module name is provided
     *
     * @param null $modName
     * @return string
     */
    function getSourceModulePath($modName=null) {
        return $this->getConfig()->assert('source_module_path').($modName ? "/".$modName : "");;
    }

    /**
     * Get the live module path from the config 'module_path' setting, this adds the module name if provided
     *
     * @param null $modName
     * @return string
     */
    function getLiveModulePath($modName=null) {
        return $this->getPath()."/".$this->getConfig()->assert('module_path').($modName ? "/".$modName : "");
    }

    /**
     * Get the web app path, this adds the PHP file if provided
     *
     * @param null $modName
     * @return string
     */
    function getWebAppPath($phpFile="") {
        return $this->getPath()."/vendor/clinicle/web/public/application/$phpFile";
    }

    /**
     * Returns true if the config setting 'live' is set in the config
     *
     * @return bool
     */
    function isLive() {
           return $this->config->get('live') ? true : false;
    }
	
	function getInstalledVersion() {
		if(!$this->version) {
			 
			 $this->version = $this->getSystemVersion();
		}
		return $this->version;
	 }
	function isCorrectVersion() {
		if (\ADD::db()->getVersion()==$this->getInstalledVersion()) {
			return true;
        }
        return false;
	}
	
	function getSystemVersion() {
		$path = $this->path."/vendor/clinicle/core";
		$prevPath = getcwd();
		chdir($this->path);
		$output = array();

        try {
            $hgBinary = $this->config->assertSetting("hg_binary");
            if(!file_exists($hgBinary)) {
                throw new FileNotFoundException("Mercurial binary ".$hgBinary." does not exist");
            }
            if($this->config->get("live")) {
                exec($this->config->assertSetting("hg_binary") . " parents --template '{latesttag}'", $output);
            }
            else {
                exec($this->config->assertSetting("hg_binary") . " branch", $output);
            }
        }
        catch(\Exception $ex) {
            chdir($prevPath);
            throw $ex;
        }
        chdir($prevPath);
		if(!isset($output[0])) {
		    return "v?.?.?";
        }
        return $output[0];
	}

    function useCleanRegistry() {
        return $this->cleanRegistry;
    }

    function setCleanRegistry($cleanRegistry) {
        $this->cleanRegistry = $cleanRegistry;
    }
}
