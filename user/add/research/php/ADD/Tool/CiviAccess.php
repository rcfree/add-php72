<?php


class ADD_Tool_CiviAccess {
	private $_url = null;
	private $_defaultParams = array();
	private $_client = null;

	function __construct($url,$siteKey,$apiKey,$defaultParams=array("json"=>1,"sequential"=>1,"debug"=>1)) {
		$this->_url = $url;
		$this->_path="sites/all/modules/civicrm/extern/rest.php";
		
		$defaultParams['key']=$siteKey;
		$defaultParams['api_key']=$apiKey;
		$this->_defaultParams=$defaultParams;
	}
	
	function get($entity,$params) {
		return $this->_access($entity,"get",HTTP_Request2::METHOD_GET,$params);
	}
	function create($entity,$params) {
		return $this->_access($entity,"create",HTTP_Request2::METHOD_POST,$params);
	}
    function delete($entity,$params) {
        return $this->_access($entity,"delete",HTTP_Request2::METHOD_POST,$params);
    }
	private function _access($entity,$action,$method,$params) {
        ADD::log()->info("$action $entity ".json_encode($params));
		$params = array_merge($this->_defaultParams,$params);
		$params['entity']=$entity;
		$params['action']=$action;
		if (!$this->_url) throw new ADD_ToolException("No 'url' provided to CiviAccess class");
		$request = new HTTP_Request2($this->_url."/".$this->_path,$method);
		foreach($params as $pname => $pvalue) {
			$request->getUrl()->setQueryVariable($pname,$pvalue);
		}
		$response = $request->send();
		$body = json_decode($response->getBody(),true);
		
		if ($body['is_error']==1) {
			throw new ADD_ToolException("CiviAccess error:".$body['error_message']."entity:$entity, action:$action, params:".json_encode($params));
		}
		if ($action=="create") {
			return $body['values'];
		}
		if(!$body) {
			throw new ADD_ToolException("CiviAccess error: empty body text returned");
		}		
		$values = $body['values'];
		return array($values,$body['count'],isset($values[0]) ? ADD_Util::getArrayValue("id",$values[0]) : null);
	}
}
?>