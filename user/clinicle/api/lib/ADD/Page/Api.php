<?php

class ADD_Page_Api extends ADD_Page { 
    function isWebContent() {
    	return false;
	}
	function isFormOnly() {
		return true;
	}
	function createMenuPath() {
        return "api/".$this->getName();
	}
        
	function createOutput($values=array()) {
		$web = ADD::web();
		$content = ADD::web()->newContent('Json');
	      $web = ADD::web();
	      
	      try {
	             $content = ADD::web()->newContent('Json');
	             $data = $this->apiOutput($values);
				 if(!isset($data['data'])) {
				 	$data=array("data"=>$data);
				 }
	             if ($web->getMessages()) {
	                   $data['message']=1;
	                   $data['message_text']=implode("\n",$web->getMessages());
	             }
	             if ($web->getWarnings()) {
	                   $data['warning']=1;
	                   $data['warning_text']=implode("\n",$web->getWarnings());
	             }
                 
	            
	      }
	      catch(Exception $ex) {
	             $data["error"]=1;
	             $data["error_text"]=$ex->getMessage();
	             if(ADD::sys()->config('stacktrace')) $data['error_trace']=$ex->getTraceAsString();
	      }
          if(ADD::sys()->config("debug")) {
             $data['debug']=ADD::outputDebug();
         }
	      $content->setJsonData($data);
   		 return $content;
	}
}
