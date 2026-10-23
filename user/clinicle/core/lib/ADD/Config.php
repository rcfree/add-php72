<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of Config
 *
 * @author rcf8
 */

class ADD_Config {
    private $_fileName = null;
    private $_config = array();
    private $_settings = array();
    private $_sections = array();
    private $_sectionName;
    
    function __construct($config=null,$ignoreArrays=false) {
        $this->_config = $config;
        if ($config) $this->_parseConfig($ignoreArrays);
    }
    function getSectionName() {
        return $this->_sectionName;
    }
    function setSectionName($sectionName) {
        $this->_sectionName = $sectionName;
    }
    function loadFile($fileName) {
        if (!file_exists($fileName)) return false;
        $this->_fileName = $fileName;
        $this->_config = parse_ini_file($fileName,true);
        $this->_parseConfig();
        return true;
    }
    function setConfig($config) {
        $this->_config = $config;
    }
    function getSection($name) {
        return isset($this->_sections[$name]) ? $this->_sections[$name] : null;
    }
	function hasSection($name) {
		return isset($this->_sections[$name]);
	}
	
    function getSetting($name,$default=null) {
        return ADD_Util::val($name,$this->_settings,$default);
    }
    
    function assertSetting($name) {
        if (!isset($this->_settings[$name])) throw new ADD_SettingNotDefinedException("Setting '$name' is not set and is mandatory");
        return $this->_settings[$name];
    }
    function getSectionSettings($name) {
        return $this->getSection($name)->getSettings();
    }
    
    function getSettingList($name,$sep=" ") {
        $value = ADD_Util::val($name,$this->_settings,null);
        if (!$value) return array();
        return explode($sep,$value);
    }
    function setSetting($name,$value) {
        $this->_settings[$name]=$value;
        $this->_config[$name]=$value;
    }
    function getSettings() {
        return $this->_settings;
    }
    function getSections() {
        return $this->_sections;
    }
    function _parseConfig($ignoreArrays=false) {
        foreach($this->_config as $key => $value) {
            if(is_array($value) && !$ignoreArrays) {
                $conf = new ADD_Config($value,true);
                $conf->setSectionName($key);
                $this->_sections[$key]=$conf;
            }
            else {
                $this->_settings[$key]=$value;
            }
        }
    }
    function getConfig($name=null) {
        if (!$name) return $this->_config;
        return ADD_Util::val($name,$this->_config);
    }
}
