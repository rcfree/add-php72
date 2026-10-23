<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 17/03/2015
 * Time: 14:24
 */

namespace ClinicLE\Common\Config;
use ClinicLE\Common\Exception\SettingNotDefinedException;
use ClinicLE\Common\Util;

class AbstractConfig extends \ArrayObject {
    protected $_config = array();
    private $_settings = array();
    private $_sections = array();
    private $_sectionName;

    function offsetSet($name,$value) {
        return $this->set($name,$value);
    }

    function offsetGet($name) {
        return $this->get($name);
    }
    function getSectionName() {
        return $this->_sectionName;
    }
    function setSectionName($sectionName) {
        $this->_sectionName = $sectionName;
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

    function get($name,$default=null)
    {
        return $this->getSetting($name, $default);
    }
    function all() {
        return $this->getSettings();
    }
    function getSetting($name,$default=null) {
        return Util::val($name,$this->_settings,$default);
    }

    function assertSetting($name) {
        if (!isset($this->_settings[$name])) throw new SettingNotDefinedException("Setting '$name' is not set and is mandatory:<pre>".print_r($this,1)."</pre>");
        return $this->_settings[$name];
    }
	
	function assertSection($sectionName) {
		$section = $this->getSection($sectionName);
		if(!$section) {
			throw new SettingNotDefinedException("Section '$sectionName' is not defined and is mandatory:<pre>".print_r($this,1)."</pre>");
		}
		return $section;
	}
	function assertSectionSettings($sectionName) {
		$section = $this->assertSection($sectionName);
		return $section->getSettings();
	}
    function assert($name) {
        return $this->assertSetting($name);
    }
    function getSectionSettings($name) {
        return $this->getSection($name)->getSettings();
    }

    function getSettingList($name,$sep=" ") {
        $value = Util::val($name,$this->_settings,null);
        if (!$value) return array();
        return explode($sep,$value);
    }

    function getList($name,$sep=" ") {
        return $this->getSettingList($name,$sep);
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
            if(is_array($value) && !isset($value[0]) && !$ignoreArrays) {
                $conf = new ArrayConfig($value,true);
                $conf->setSectionName($key);
                $this->_sections[$key]=$conf;
				$this->_settings[$key]=$value;
            }
            else {
            $this->_settings[$key]=$value;
        }
    }
    }
    function getConfig($name=null) {
        if (!$name) return $this->_config;
        return Util::val($name,$this->_config);
    }
    function set($name,$value) {
        $this->setSetting($name,$value);
    }
	function has($name) {
		return isset($this->_settings[$name]);
	}
}