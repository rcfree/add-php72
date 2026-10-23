<?php

use Codebreaker\Logic\SpellCollate;
use ClinicLE\Service\Remote\AccessFactory;

class ADD_Page_Api_TransferCB extends ADD_Page_Api {
	function createMenuPath() {
       return "api/transfercb";
    }
	
	function apiOutput() {
		$parentId = ADD::sys()->getParam('parentid');
		$sourceIdent = ADD::sys()->getParam('sourceident');
		$sourceId = ADD::sys()->getParam('sourceid');
		$pim = ADD::sys()->getParam('pim');
		$sourcePim = ADD::sys()->getParam('sourcepim');
		$factory = new AccessFactory();
		$system = ADD::sys();
		$config = $system->getConfig()->assertSection("Codebreaker");
		$access = $factory->newAccess($config->assertSetting('codebreaker_url'), $config->assertSetting('codebreaker_apikey'));
        $spellCollate = new SpellCollate($access);
		$data = $spellCollate->updateItems($sourceIdent, $sourceId, $parentId, $sourcePim);
		return $data;
	}
}
?>