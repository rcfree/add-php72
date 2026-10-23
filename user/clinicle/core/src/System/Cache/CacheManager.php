<?php
namespace ClinicLE\System\Cache;
class CacheManager {
	function __construct($cache,$system) {
		$this->cache = $cache;
		$this->system = $system;
	}
 function isCacheSetup() {
 	return $this->cache->exists("modulelist");
 }
 private function generatePageKey($page) {
 	return "module:".$page->getModule()->getName().":page:".$page->getName();
 }
 function setupFormFromCache($formComponent) {
 	$page = $formComponent->getPage();
	$mod = $page->getModule();
 	$pageKey = "module:".$mod->getName().":form:".$page->getName();
	$module = $page->getModule();
 	$fieldContent = $this->cache->get($pageKey);
 	if(!$fieldContent) {
 		return;
	}
	$formComponent->setFieldContent($fieldContent);
	$formComponent->setSetup(true);
	return;
 }
 function isFormCached($page) {
 	$mod = $page->getModule();
 	$pageKey = "module:".$mod->getName().":form:".$page->getName().":entry";
	return $this->cache->exists($pageKey);
 }
 function clear() {
 	$this->cache->clearAll();
 }
 function putFormInCache($formComponent) {
 	$page = $formComponent->getPage();
	$mod = $page->getModule();
 	$pageKey = "module:".$mod->getName().":form:".$page->getName();
	
 	$this->cache->put($pageKey,$formComponent->getFieldContent());
	$this->cache->put($pageKey.":entry",1);
 }
 function setupModulesInCache() {
 	$this->system->initModules();
	$moduleList = array();
	
	foreach($this->system->getModules() as $module) {
		$this->cache->put("module:".$module->getName(),$module);
		$moduleList[] = $module->getName();
	}
	$this->cache->put("modulelist",$moduleList);
/*
	foreach($this->system->getModules() as $module) {
		foreach($module->getPages() as $page) {
			try {	
				$page->setupComponents();
			}
			catch(\Exception $ex) {
			}
			$this->cache->put($this->generatePageKey($page),$page);
		}
	}
 * 
 */
 }
 function populateModulesFromCache() {
	$moduleList2 = $this->cache->get("modulelist");
	foreach($moduleList2 as $moduleName) {
		$module2 = $this->cache->get("module:".$moduleName);
		$this->system->getModules()->add($module2);
	}
 }
}
