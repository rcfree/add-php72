<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Tool_Emailer {

	private $_lineFeed = "\n";
	private $_body=array();
	private $_subjectLine = "";
	
	function __construct($subjectLine, $fromLabel, $fromAddress) {
		$this->_subjectLine = $subjectLine;
		$this->_fromAddress = $fromAddress;
		$this->_fromLabel = $fromLabel;
	}
	
	function setLineFeed($lineFeed) {
		$this->_lineFeed = $lineFeed;
	}
	function addToBody($bodyLine) {
		$this->_body[]=$bodyLine;
	}
	function setBody($body) {
		if(!is_array($body)) {
			$body = explode($this->_lineFeed,$body);
		}
		$this->_body = $body;
	}
	function sendTo($toAddress) {
		
        $sent = mail($toAddress, $this->_subjectLine, implode($this->_lineFeed,$this->_body), "From: ".$this->_fromLabel . " <".$this->_fromAddress.">");

        if (!$sent) {
            throw new ADD_EmailSendFailException("Unable to send the email '".$this->_subjectLine."' to ".$this->_fromAddress);
        }
    }
}