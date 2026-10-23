<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\Service\Remote\AccessFactory;

class ADD_Page_Api_RemoteEncounters extends ADD_Page_Api {
	private $filters = array();
	private $_form;
	private $_page;
	
	function isOnlyLabelOutput() {
		return false;
	}
		
	function apiOutput() {
		$add = ADD::sys();
		$site = $add->getParam("site");
		$remoteConfig = $add->getConfig()->assertSection("Remote:".$site);
		if($remoteConfig->has('disabled')) {
			ADD::web()->addWarning("Remote link disabled for site ".$site);
			$data['data']=array();
			return $data;
		}
		
		$factory = new AccessFactory();
		$localPatient = ADD::db('patient')->getPatient(ADD::sys()->getPatientId());
		
		$access = $factory->newAccess($remoteConfig->assertSetting('url'), $remoteConfig->assertSetting('apikey'));
        $remotePatient = null;

        try {
            $remotePatientData = $access->getPatientWithIdentifier($localPatient['identifier']);
            $remotePatient = ADD_Util::val("data", $remotePatientData);
        }
        catch(\ClinicLE\Service\Remote\Exception\RemoteDataException $ex) {
            ADD::web()->addWarning("Patient may not exist in remote ".$remoteConfig->get("title",$site)." 
            system");
        }
        catch(\Exception $ex) {
            ADD::web()->addWarning("Error getting patient from remote ".$remoteConfig->get("title",
                    $site)." 
            system");
            CLE::info("web:",ADD::web()->getWarnings());
        }
        $rows = array();
        try {
            $rows = $this->getEncountersForRemotePatient($remotePatient,$access, $remoteConfig);
        }
        catch(\Exception $ex) {
            ADD::web()->addWarning("Error getting data from remote ".$remoteConfig->getSetting("title",
                    $site)." 
            system");
        }

		$data['data']=$rows;
		$data['total']=count($rows);
		$data['first']=count($rows)==0 ? 0 : 1;
		$data['last']=count($rows);
		
		return $data;
	}
    private function getEncountersForRemotePatient($remotePatient, $access, $remoteConfig) {
        $add = ADD::sys();
        if(ADD_Util::isEmpty($remotePatient)) {
            return array();
        }

        return $access->getEncountersUsingPim($remotePatient['id'], $add->getParam('pim'), $add->getParams());
    }
}
