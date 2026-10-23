<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RemoteListOption
 *
 * @author rob
 */
use ClinicLE\Service\Remote\AccessFactory;
use Codebreaker\Logic\SpellCollate;

class ADD_Component_WardspellList extends ADD_Component implements ADD_Component_AjaxInterface {
	function ajaxFunction($values, ADD_Page $callPage) {
        $factory = new AccessFactory();
		$system = ADD::sys();
		$config = $system->getConfig()->assertSection("Codebreaker");
		
		$access = $factory->newAccess($config->assertSetting('codebreaker_url'), $config->assertSetting('codebreaker_apikey'));
        $spellCollate = new SpellCollate($access);
		
		$pid = ADD::sys()->getPatientId();
		$patient = ADD::db('patient')->getPatient($pid);
		
		try {
			$collatedSpells = $spellCollate->collateSpells($patient['identifier']);
		}
		catch(Exception $ex) {
			$collatedSpells = array(array("value"=>"","label"=>$ex->getMessage().":".$ex->getTraceAsString()));
			return array("select_ward_spell"=>array("options"=>$collatedSpells),"messages"=>array("debug"=>ADD::outputDebug()));
		}
		if(count($collatedSpells)==0) {
			$collatedSpells = array(
				array("value"=>"","label"=>"No ward spells in Codebreaker for this patient")
			);
		}
		else {
			array_unshift($collatedSpells,array("value"=>"","label"=>"-- select a ward spell --"));		
		}
        return array("select_ward_spell"=>array("options"=>$collatedSpells),"messages"=>array("debug"=>ADD::outputDebug()));
    }

    function ajaxInputs(ADD_Page $callPage) {
        return array();
    }
   
}
