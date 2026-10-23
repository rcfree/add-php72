<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 15/12/16
 * Time: 14:07
 */

namespace ClinicLE\System\Util;
use CLE;
use ClinicLE\System\Exception\ExecuteFailedException;


class Execute
{
    private $command;

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    private $exceptionOnFail=false;
    private $exceptionMessage="Unexpected error occurred";
    private $outputLogged=false;
    private $verbose=false;

    /**
     * @return boolean
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }


    /**
     * @return string
     */
    public function getExceptionMessage()
    {
        return $this->exceptionMessage;
    }

    /**
     * @param string $exceptionMessage
     */
    public function setExceptionMessage($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
    }

    /**
     * @return boolean
     */
    public function isOutputLogged()
    {
        return $this->outputLogged;
    }

    /**
     * @param boolean $outputLogged
     */
    public function setOutputLogged($outputLogged)
    {
        $this->outputLogged = $outputLogged;
    }

    /**
     * @return boolean
     */
    public function isExceptionOnFail()
    {
        return $this->exceptionOnFail;
    }

    /**
     * @param boolean $exceptionOnFail
     */
    public function setExceptionOnFail($exceptionOnFail)
    {
        $this->exceptionOnFail = $exceptionOnFail;
    }

    function __construct($command) {
        $this->command = $command;
    }
    function exec() {
        $execCommand = $this->getCommand();
        if(is_array($execCommand)) {
            $execCommand = implode(" ",$execCommand);
        }
        $output = array();
        $returnVar = 0;

        exec($execCommand,$output,$returnVar);

        if($this->isOutputLogged()) {
            CLE::log($this)->info("Output:".implode("\n",$output));
        }
        if ($returnVar!=0 && $this->isExceptionOnFail()) {
            throw new ExecuteFailedException($this->exceptionMessage,$this->command, $output,$returnVar);
        }

        return array($output,$returnVar);
    }
}