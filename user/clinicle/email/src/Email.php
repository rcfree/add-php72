<?php

namespace ClinicLE\Service\Email;

class Email {

	private $lineFeed = "\n";
	private $body=array();
	private $subjectLine = "";
	private $fromAddress;
	private $fromLabel;
	private $printfArgs=array();
	
	function __construct($subjectLine, $fromLabel, $fromAddress) {
		$this->subjectLine = $subjectLine;
		$this->fromAddress = $fromAddress;
		$this->fromLabel = $fromLabel;
	}
	
	function setLineFeed($lineFeed) {
		$this->lineFeed = $lineFeed;
	}
	function addToBody($bodyLine) {
		$this->body[]=$bodyLine;
	}
	function setBody($body) {
		if(!is_array($body)) {
			$body = explode($this->lineFeed,$body);
		}
		$this->body = $body;
	}
	function getBodyAsText() {
		return vsprintf(implode($this->lineFeed,$this->body),$this->printfArgs);
	}
	function getSubjectLine() {
		return $this->subjectLine;
	}
	function getFromLine() {
		return "From:".$this->fromLabel . " <".$this->fromAddress.">";
	}
	function getFromAddress() {
		return $this->fromAddress;
	}
	function setPrintfArgs(array $printfArgs) {
		$this->printfArgs = $printfArgs;
	}
}