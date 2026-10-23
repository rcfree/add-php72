<?php
/*
 * Entity representing CiviCRM Activity Status and actions extends the OptionEntity
 * which deals with retrieving option values from specific option groups
 */

class LRB_CiviTool_Entity_WordReplace extends LRB_CiviTool_Entity {
	function getLocale() {
		return "en_GB";
	}
	function get() {
		list($domain, $lString) = $this->_getDomain();
		$enabledLs = $lString[$this->getLocale()]['enabled'];
		$style =  $this->getOption(LRB_CiviTool_Entity::STYLE);
		$exactMatch = $style == "exact" ? true : false;
		$data  = $exactMatch ? 
			$enabledLs['exactMatch'] :
			$enabledLs['wildcardMatch'];
		$orig = $this->getOption(LRB_CiviTool_Entity::LABEL);
		$repl = $this->getOption(LRB_CiviTool_Entity::NAME);
		
		if (isset($data[$orig]) && $data[$orig]==$repl) {
			return array(
				"original" => $orig,
				"replacement" => $repl,
				"enabled" => 1,
				"exact_match" => $exactMatch
			);
		}
	}
	
	function _getDomain() {
		require_once "CRM/Core/BAO/Domain.php";
		$locale = $this->getLocale();
		$defaults = array();
		$params = array();
		$domain = CRM_Core_BAO_Domain::retrieve($params, $defaults);
		
		$lstrings = $domain->locale_custom_strings;
		$localeString = $lstrings ? unserialize($lstrings) 
		: array("$locale"=>array("enabled"=>array("exactMatch"=>array(),"wildcardMatch"=>array())));
		return array($domain,$localeString);
	}
	
	function exists() {
		$entity = $this->get();
		return $entity ? true : false;
	}
	
	function create() {
		list($label,$name) = $this->getLabelAndName();
		
		if ($this->exists()) throw new LRB_CiviToolException("Cannot create WordReplace '$label' => '$name' it already exists");
		 
		list($domain, $lString) = $this->_getDomain();
		
		$style =  $this->getOption(LRB_CiviTool_Entity::STYLE);
		$exactMatch = $style == "exact" ? true : false;
		
		$lString[$this->getLocale()]['enabled'][$exactMatch ? 'exactMatch' : 'wildcardMatch'][$label]=$name;
		
		$params = array("locale_custom_strings"=>serialize($lString));
		CRM_Core_BAO_Domain::edit($params, $domain->id);
	}
	
	function delete() {
		list($label,$name) = $this->getLabelAndName();
		
		if (!$this->exists()) throw new LRB_CiviToolException("Cannot delete WordReplace '$label' => '$name' it does not exist");
		 
		list($domain, $lString) = $this->_getDomain();
		
		$style =  $this->getOption(LRB_CiviTool_Entity::STYLE);
		$exactMatch = $style == "exact" ? true : false;
		$locale = $this->getLocale();
		$matches = $lString[$locale]['enabled'][$exactMatch ? 'exactMatch' : 'wildcardMatch'];
		unset($matches[$label]);
		$lString[$locale]['enabled'][$exactMatch ? 'exactMatch' : 'wildcardMatch']=$matches;
		print_r($lString);
		$params = array("locale_custom_strings"=>serialize($lString));
		CRM_Core_BAO_Domain::edit($params, $domain->id);
	}
	
}