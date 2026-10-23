<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 15/12/16
 * Time: 14:07
 */

namespace ClinicLE\System\Util;
use CLE;


class CommandExecute
{
    private $command;
    private $options;
    private $stdOutFile="/dev/null";
    private $stdErrFile="/dev/null";
    private $appended = false;
    private $profile;
    private $path;
    private $logConfigFile;

    /**
     * @return mixed
     */
    public function getLogConfigFile()
    {
        return $this->logConfigFile;
    }

    /**
     * @param mixed $logConfigFile
     */
    public function setLogConfigFile($logConfigFile)
    {
        $this->logConfigFile = $logConfigFile;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    function __construct($manageCommand, $options) {
        $this->command = $manageCommand;
        $this->options = $options;
        $this->path = CLE::sys()->getPath();
        $this->profile = CLE::sys()->getProfile();
    }
    function setStdOutFile($stdOutFile) {
        $this->stdOutFile = $stdOutFile;
    }
    function setStdErrFile($stdErrFile) {
        $this->stdErrFile = $stdErrFile;
    }
    function setAppended($appended) {
        $this->appended = $appended;
    }
    function isAppended() {
        return $this->appended;
    }
    function executeInBackground() {
        $execCommand = $this->_createCommandString();
        if($this->appended) {
            if(!file_exists($this->stdOutFile)) {
                touch($this->stdOutFile);
            }
            if(!file_exists($this->stdErrFile)) {
                touch($this->stdErrFile);
            }
        }
        $execString = $execCommand.' '.($this->appended ? '>>' : '>').$this->stdOutFile.' '.($this->appended ? '2>>' : '2>').$this->stdErrFile.' & echo $!';
        CLE::info("execString",$execString);
        exec($execString,$output,$returnVar);
        return array($output,$returnVar);
    }

    function _createCommandString() {
        $command = $this->command;
        $options=array();
        foreach($this->options as $name => $option) {
            $options[]=$name.$option;
        }
        return "export ADD_PATH=".$this->path."; export ADD_PROFILE=".$this->profile."; export CLE_LOG_CONFIG=".$this->logConfigFile."; " . $this->path
        ."/vendor/bin/clinicle $command ".implode(" ",$options);
    }
}