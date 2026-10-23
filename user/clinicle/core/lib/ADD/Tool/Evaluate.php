<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Evaluate
 *
 * @author rcf8
 */
	
		
class ADD_Tool_Evaluate {
	private $_expression;
	function __construct($expression) {
		$this->_expression=$expression;
	}
	function evaluate($prefix="ADD_Field_Util") {
		if (empty($this->_expression)) return "";
		list($actualFunctions,$functionNames, $isUnquotedString) = $this->_extractFunctions();
		if($isUnquotedString) return $this->_expression;
		
		foreach($functionNames as $functionName) {
			if(!$prefix::hasFunction($functionName)) {
				throw new ADD_UtilException("Function '$functionName' is not a recognised ADD field function");
			}
		}
		$expression = $this->_expression;
		foreach($actualFunctions as $actualFunction) {
			$funcValue = $this->_evaluateCode($prefix."::".$actualFunction);
			if($funcValue==null) return "";
			if(is_string($funcValue)) $funcValue="\"".$funcValue."\"";
			if(is_array($funcValue)) {
				$expression = str_replace($actualFunction,var_export($funcValue,1),$expression);
			}
			else {
				$expression = str_replace($actualFunction,$funcValue,$expression);
			}
		}
		
		return $this->_evaluateCode($expression);
	}
	private function _getFunctionContent($tokens, $startPos) {
		$currBrac=0;
		$functionContent = "";
		for($t=$startPos;$t<count($tokens);$t++) {
			$token = $tokens[$t];
			if(is_scalar($token)) {
				
				$functionContent.=$token;
			}
			else {
				$functionContent.=$token[1];
			}
			if($token=="(") $currBrac++;
			if($token==")") $currBrac--;
			if($token==")" && $currBrac==0) return $functionContent;
		}
	}
	private function _extractFunctions() {
		$matches=array();
		$tokens = token_get_all("<?php ".$this->_expression." ?>");
		
		if(count($tokens)==4) {
			$token = $tokens[1];
			if(is_array($token) && $token[0]==T_STRING) {
				return array(array(),array(),true);
			}
		}
		
		$tokenCount=0;
		
		$functionNames = array();
		$functions = array();
		
		for($t=0;$t<count($tokens);$t++) {
			$token = $tokens[$t];
			$function=$token[0];
			if(is_array($token) && $token[0]==T_STRING) {
				if ($tokens[$t+1]=="(") {
					$t+=1;
					array_unshift($functionNames,$token[1]);
					array_unshift($functions,$token[1].$this->_getFunctionContent($tokens,$t));
				}
			}
		}
		
		return array(array_unique($functions),array_unique($functionNames),false);
	}
	
	private function _evaluateCode($expression) {
		$val = null;
		$err = null;
		$errTrace = null;
		$evalString = 'set_error_handler(function($errno,$errstr) {
throw new Exception($errstr);
}); try { $val = '.$expression.'; restore_error_handler(); } catch(Exception $ex) { $err = $ex->getMessage(); $errTrace = $ex->getTraceAsString(); $val=null; restore_error_handler(); }';
		eval($evalString);
		if ($err) {
			if (ADD::sys()->hasState(ADD_System::EVAL_EXCEPTION)) throw new ADD_FieldException($err);
			ADD::log()->error("Evaluate error:".$err. "\n".$errTrace);
			ADD::web()->addError("Error evaluating field. Check log");
			return null;
		}
		return $val;
	}
}
