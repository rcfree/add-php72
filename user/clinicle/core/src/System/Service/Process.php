<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Service;
use ADD;
use ClinicLE\System\Exception\ExecuteFailedException;

class Process {
	function exec($command) {
		$output = null;
		$return_var = null;
		exec($command, $output, $return_var);
		return array($output,$return_var);
	}
	function executeWithException($command,$showOutput=false,$exceptionMessage="Problem executing command") {
		if(is_array($command)) $command = implode(" ",$command);
		$log = ADD::log();
				
		$returnVar = 0;
		$output = array();
		exec($command,$output,$returnVar);
		if($showOutput) {
			$log->info("Output:".implode("\n",$output));
		}
		if ($returnVar!=0) {
			throw new ExecuteFailedException($exceptionMessage,$command, $output,$returnVar);
		}
		return $output;
	}
	function execWithException($command,$output,$returnVar,$exceptionMessage) {
		if(is_array($command)) $command = implode(" ",$command);
		$log = ADD::log();
				
		$returnVar = 0;
		$output = array();
		exec($command,$output,$returnVar);
		if($showOutput) {
			$log->info("Output:".implode("\n",$output));
		}
		if ($returnVar!=0) {
			throw new ExecuteFailedException($exceptionMessage,$command, $output,$returnVar);
		}
		return array($output,$returnVar);
	}
}