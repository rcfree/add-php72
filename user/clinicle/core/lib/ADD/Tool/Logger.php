<?php 
class ADD_Tool_Logger {
    private $errors=false;
    private $warnings=false;
    private $failed=false;
    function __construct(Logger $logger) {
        $this->logger = $logger;
        return $this;
    }
    public function __call($method_name,$args) {
        $logger = $this->logger;
        
        //non-standard approach to deal with debug trace issue in Logger
        //call_user_func_array() did not work for this
        $arg1 = isset($args[0]) ? $args[0] : null;
        $arg2 = isset($args[1]) ? $args[1] : null;
        $arg3 = isset($args[2]) ? $args[2] : null;
        
        $output = $logger->$method_name($arg1,$arg2,$arg3);
        
        switch($method_name) {
            case "error":
                $this->errors=true;
                break;
            case "warn":
                $this->warnings=true;
                break;
            case "fatal":
                $this->failed = true;
                break;
        }
        return $output;
    }
    
    public function complete() {
        if ($this->failed) {
            $this->state("FAILED");
            return;
        }
        if($this->errors) {
            $this->state("ERRORS");
            return;
        }
        if($this->warnings) {
            $this->state("WARNINGS");
            return;
        }
        $this->state("SUCCESS");
    }
    public function state($state) {
        $this->logger->info("STATE:".$state);
    }
    public function running() {
        $this->state("RUNNING");
    }
    public function pending() {
        $this->state("PENDING");
    }
    
    
    public function handleExit($exitcode) {
        $this->logger->info("EXIT:".$exitcode);
    }
}