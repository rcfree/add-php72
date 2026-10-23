<?php
namespace ClinicLE\Episode\Resource;
use ClinicLE\Patient\Resource\PatientEvents;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\Module\AbstractResource;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Episode\Data\EpisodeGroup;
use ClinicLE\Install\Event\InstallEvents;
use ClinicLE\Manage\Resource\ManageServices;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Manage\Event\ManageEvents;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClientResource
 *
 * @author rob
 */
use ClinicLE\Episode\Resource\EpisodeServices;

class EpisodeResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$services[EpisodeServices::EPISODE_GROUP]=function($c) {
            return new EpisodeGroup($c[CoreServices::MAIN_CONNECTION]);
		};
		$events->addListener(InstallEvents::BEFORE_DICTIONARIES,array($this,"onBeforeRun"));
		$events->addListener(InstallEvents::INSTALL_FORM,array($this,"onInstallForm"));

        $events->addListener(PatientEvents::BEFORE_PURGE_PATIENT,array($this,"onBeforePurgePatient"));

        $events->addListener(PatientEvents::BEFORE_DELETE_PATIENT,array($this,"onBeforeDeletePatient"));

		CLE::svc(CoreServices::FIELD_FUNCTIONS)->addFunction("episode_get",array($this,"fieldFunctionEpisodeGet") );
		$events->addListener(ManageEvents::DATA_VIEW,array($this,"onDataView"));
	}

	function onBeforePurgePatient($event) {
        $conn = CLE::svc(CoreServices::MAIN_CONNECTION);
        $conn->execute("delete from `section` where episode_id in (select id from episode where patient_id=?)",$event->getPatientId());
        $conn->execute("delete from `episode` where patient_id=?",$event->getPatientId());
        CLE::log()->info("Purge episodes from patient id:".$event->getPatientId());
    }
    function onBeforeDeletePatient($event) {
        $conn = CLE::svc(CoreServices::MAIN_CONNECTION);
        $conn->execute("update `episode` set is_deleted=1 where patient_id=?",$event->getPatientId());
        CLE::log()->info("Delete episodes from patient id:".$event->getPatientId());
    }
	function fieldFunctionEpisodeGet($fieldName) {
			$temp = explode(":",$fieldName);

			if(count($temp)!=2) {
				CLE::web()->addWarning("'episode_get' function must have page specified");
			}
			$cle = CLE::sys();
			$am = $cle->getAccessManager();
			$currentPage = $am->getCurrentPage();
			$episodePage = $currentPage->getModule()->getPage($temp[0]);
			$encounter = CLE::svc(EpisodeServices::EPISODE_GROUP)->getLinkedSectionEncounterInEpisode($episodePage->getSetting('encounter'),$cle->getParam('eid'));
			$encounter->retrieve();
			
			if(!$encounter->exists() || Util::isEmpty($encounter->getObsValue($temp[1]))) {
				$emptyWarning = $currentPage->getSetting("empty_warning.".$temp[1],"Cannot populate '".Util::nameToLabel($temp[1])."' value as ".$episodePage->getSetting('title')." record does not exist");
				CLE::web()->addWarning($emptyWarning);
				return null;
			}
			
			return $encounter->getObsValue($temp[1]);
	}
	function onDataView($event) {
		if($event->getOptions()->get("r")!="epi") {
			return;
		}
		$mainConnection = CLE::svc(CoreServices::MAIN_CONNECTION);
		
		$sql = "select e.*, et.name from episode e left join episode_type et on e.episode_type_id = et.id";
		if($event->getOptions()->get("t")) {
			$where[]=" et.name=?";
			$params[]=$event->getOptions()->get("t");
		}
		if($event->getOptions()->get("p")) {
			$where[]=" e.patient_id = ?";
			$params[]=$event->getOptions()->get("p");
		}
		if($event->getOptions()->get("i")) {
			$where[]=" e.id = ?";
			$params[]=$event->getOptions()->get("i");
		}
		$rows = $mainConnection->fetchAll($sql.($where ? " where ".implode(" and ",$where) : ""),$params);
		$event->setTitle("episodes of type ".$event->getOptions()->get("t") . " (for patient ". $event->getOptions()->get("p") .")");
		$event->setSubTitle("episode");
		$event->setData($rows);	
	}
	function onBeforeRun($event) {
		$migHandler = CLE::svc(ManageServices::MIGRATION_HANDLER);
		$epiModule = CLE::sys()->getModule("clinicle/episode");
		if(!$epiModule) {
			return;
		}
		$migHandler->migrateUsingFile($epiModule->getPath()."/sql/episode.sql");
	}
	
	function onInstallForm($event) {
		$episodeTypeName = $event->getPage()->getSetting("episode");
		if(!$episodeTypeName) {
			return;
		}
		$epiGroup = CLE::svc(EpisodeServices::EPISODE_GROUP);
		$episodeType = $epiGroup->getEpisodeType($episodeTypeName);
		if(!$episodeType) {
			$epiGroup->addEpisodeType($episodeTypeName);
			CLE::log($this)->info("Added new episode type '$episodeTypeName'");
		}
		else {
			CLE::log($this)->info("Episode type '$episodeTypeName' exists");
		}
	}
}
