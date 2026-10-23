<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System;

class Notices {
    private $messages;
    private $errors;
    private $warnings;
	private $system;

	function setSystem(System $system) {
		$this->system = $system;
	}
	function addMessage($message, $noDuplicates = null) {
        if ($noDuplicates && $this->messages && in_array($message, $this->messages))
            return;
        if (!$this->messages)
            $this->messages = array();
        $this->messages[] = $message;
    }

    function setMessages($messages = null) {
        $this->messages = $messages;
    }

    function setWarnings($warnings = null) {
        $this->warnings = $warnings;
    }

    function setErrors($errors = null) {
        $this->errors = $errors;
    }

    function getWarnings() {
        return $this->warnings;
    }

    function getErrors() {
        return $this->errors;
    }

    function getMessages() {
        return $this->messages;
    }
	function addWarning($warning, $noDuplicates = null) {
        if ($noDuplicates && $this->warnings && in_array($warning, $this->warnings))
            return;

        if (!$this->warnings)
            $this->warnings = array();
        if ($this->system->config('log_warnings')) {
			\ADD::log()->warn($warning);
		}
        $this->warnings[] = $warning;
    }

    function addError($error, $noDuplicates = null) {
        if ($noDuplicates && $this->errors && in_array($warning, $this->errors))
            return;
        if (is_object($error)) {
        	$st = $error->getTraceAsString();
            $error = $this->errorMessage($error);
			$add = \ADD::sys();
			if(!$add->config('error_message_only')) {
				//$error = $error; . "<br/>The administrator has been notified of this error. <br/>Please contact Dr Rob Free (tel: UHL x3911, email:<a href='mailto:robert.free@uhl-tr.nhs'>robert.free@uhl-tr.nhs.uk</a>) if this message continues to appear.";
				if($add->config('email_on_error')) {
					$siteName = $add->getConfig()->getSetting('site_name','ADD');
					$siteTitle = $add->getConfig()->getSetting('site_title','Airway Disease Database');
					$user = $this->system->getAccessManager()->getUser();
					$username = is_object($user) ? $user->username : "none";
					$patientId = $this->system->getPatientId();
					$emailMsg=$error;
					if (!$add->config('stacktrace')) $emailMsg.="\n\nStacktrace:\n".$st;
					
					$emailMsg.="\n\nTime of error:".date("d/m/Y H:n.s")."\n";
					$emailMsg.="User: ".$username."\n";
					$emailMsg.="Patient: ".$patientId."\n";
					$emailMsg.="URL: ".\ADD_Util::currentPageURL()."\n";
					str_replace("<br/>","\n\n",$emailMsg);
					$emailMsg = strip_tags($emailMsg);
					$sent = mail($add->config('email_on_error'), "[".$siteName."-error] Unexpected error", $emailMsg, "From: ".$siteTitle." <".strtolower($siteName)."-noreply@xuhl-tr.nhs.uk>");
				}
			}
		}	
        if (!$this->errors)
            $this->errors = array();
        if ($this->system->config('log_errors')) \ADD::log($this)->error($error);
        $this->errors[] = $error;
    }
    function errorMessage($ex) {
		
        $add = \ADD::sys();
        $message = $ex->getMessage();
        if ($add->config('nocatch'))
            throw $ex;
        if ($add->config('stacktrace'))
            $message.="<br/>Stacktrace:<pre>" . $ex->getTraceAsString() . "</pre>";
        return $message;
    }

	
	function reset() {
		$this->warnings=array();
        $this->messages=array();
        $this->errors=array();
        $this->content=array();
	}
	
}
