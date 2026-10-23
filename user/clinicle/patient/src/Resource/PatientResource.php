<?php
namespace ClinicLE\Patient\Resource;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\Module\AbstractResource;
use ClinicLE\System\Event\ContentEvents;
use CLE;
use ADD_Content_Html;

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
use ClinicLE\Common\Util;
use ClinicLE\System\Service\CoreServices;

class PatientResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$events->addListener(ContentEvents::TOP_PANEL,array($this,"onTopPanel"));
        $events->addListener(ContentEvents::SIDE_COMMON_OPTIONS,array($this,"onSideCommonOptions"));
        CLE::svc(CoreServices::FIELD_FUNCTIONS)->addFunction("patient_get",array($this,"fieldFunctionPatientGet") );
    }
	
	function onTopPanel($event) {
        $top = $event->getContent();
		$add = CLE::sys();
        $db = CLE::db('patient');
		$am = CLE::sys()->getAccessManager();
		if(!$am->isLoggedIn()) return;
		$patientId = $add->getPatientId();
		$currPage = $add->getCurrentPage();
                if ($currPage && $currPage->hasComponent('RemovePatient')) return;
		if ($add->hasState('newPatient')) {
			return $this->_createNewPatient();
		}
			
		if ($patientId) {
			if ($db->doesPatientExist($patientId,true)) {
				$this->_createPatientSelected($top);
			}
			else {
				$this->_createPatientNoExist($top);
			}			
		}
	}

	function onSideCommonOptions($event) {
        $ppage = CLE::sys()->getPageInModule("patient","new");
        if (!$ppage->getPermission('create')) {
            return;
        }
    /*
           $newPatientAtTop = false;

           if($add->config('theme_module')) {
               $themeMod = $add->getModule($add->config('theme_module'));
               if($themeMod->getSetting('new_at_top')) {
                   $newPatientAtTop=true;
               }
           }

           if($newPatientAtTop) {
               $topBar = CLE::web()->getContent('topBar');
               $topBar->startElement("li");
               $topBar->startElement("a",array("href"=>'javascript:ADD.Client.loadController("#content","patient:new")'));
               $topBar->addTextElement("i","",array("class"=>"icon-plus-sign icon-white"));
               $topBar->addHtml("&nbsp;New patient");
               $topBar->endElement("a");
               $topBar->endElement("li");
           }
           else {
    */
       $sideCommon = $event->getContent();
       $sideCommon->startElement("a",array("class"=>"btn btn-primary btn-mini", "href"=>"/patient/new"));
       $sideCommon->addTextElement("i","",array("class"=>"icon-plus-sign icon-white"));
       $sideCommon->addHtml("&nbsp;New patient");
       $sideCommon->endElement("a");
        //   }
    }
   function _createPatientNoExist(ADD_Content_Html $top) {
       $add = CLE::sys();
       $top->addTextElement('h5', "The selected patient (".$add->getPatientId().") does not exist", array('style'=>'color:red'));
   }

   function _createNoPatients(ADD_Content_Html $top) {
       $top->addTextElement('h5', "No patients in CLE", array('style'=>'color:red'));
   }

   function _createPatientSelected(ADD_Content_Html $top) {
       $add = CLE::sys();
       $db = CLE::db('patient');
       $pid = $add->getPatientId();
       $person = $db->getPatient($pid);

       //How many IDs to generate?
       $this->addPatientDetails($top, $person);

       $this->addPatientActionButtons($top, $person, $pid);
   }

  /**
    * @param $top
    * @param $person
    */
    private function addLabelIfPatientDeceased(ADD_Content_Html $top, $person)
    {
        if ($person['deceased'] == 1) {
            $top->startElement("span", array("class" => "label label-important", "style" => "display:inline;margin-left:1em"));
            $top->addTextElement("i", "", array("class" => "icon-white icon-exclamation-sign"));

            $top->addHtml("&nbsp;Patient deceased (" . Util::val('date_of_death', $person, "no date") . ")");
            $top->endElement("span");
        }
    }

    /**
     * @param $top
     * @param $person
     */
    private function addPatientDetails(ADD_Content_Html $top, $person)
    {
    	$top->startElement("div",array("class"=>"patient-panel pp-name"));
        $top->addTextElement("span", "Patient:", array("class" => "patient-head"));
        $top->addTextElement("span", $person['first_name'] . " " . $person['last_name'], array("class" => "patient-text"));
		$this->addLabelIfPatientDeceased($top, $person);
		$top->endElement("div");
        $top->startElement("div",array("class"=>"patient-panel pp-dob"));
        $top->addTextElement("span", "DOB:", array("class" => "patient-head"));
        $top->addTextElement("span", $person['date_of_birth'], array("class" => "patient-text", "id" => "patient-date-of-birth"));
        $top->endElement("div");
		
		$top->startElement("div",array("class"=>"patient-panel pp-identifier"));
		$top->addTextElement("span", "S-number:", array("class" => "patient-head"));
        $top->addTextElement("span", $person['identifier'], array("class" => "patient-text", "id" => "patient-identifier"));
		$top->endElement("div");
        
       // $top->endElement("div");
    }

    /**
     * @param $top
     * @param $add
     * @param $person
     * @param $pid
     */
    private function addPatientActionButtons(ADD_Content_Html $top, $person, $pid)
    {
        $add = CLE::sys();
        //$top->startElement("div", array("class" => "btn-group"));
        #,array("class"=>"patient-links"));
		$patMod = $add->getModule('patient');
        $patPage = $patMod->getPage('demographics');

        
		$top->startElement("div",array("class"=>"patient-panel pp-buttons"));
           
        if ($person['is_deleted']) {
            $top->startElement("span", array("class" => "label label-warning"));
            $top->addTextElement("i", "", array("class" => "icon-white icon-warning-sign"));
            $top->addHtml("&nbsp;Patient marked as deleted&nbsp;");

        } else {
			list($patEdit, $patView) = $patPage->getPermissions(array('edit', 'view'));
            if ($patEdit || $patView) {
            	$top->addTextElement("a", "<i class='icon-pencil'></i>&nbsp;".($patView && !$patEdit ? "View" : "Edit"), 
            	array("href" => $patPage->createMenuUri(), "class" => "btn btn-small"));
                //$top->addButtonLink(($patView && !$patEdit ? "View" : "Edit") . " Demographics..", "pencil", "btn-default", $patPage->createMenuUri());
            }
            if (!$person['is_deleted']) {
                if ($patMod->getPage('delete') && $patMod->getPage('delete')->getPermission('delete')) {
                    $top->addTextElement("a", "<i class='icon-remove'></i>&nbsp;Delete", array("href" => "javascript:;", "onclick" => "deletePatient($pid);", "cle-nong"=>"","class" => "btn btn-small"));
                }
            }

            $consPage = $add->getModule('patient')->getPage('consent');
            if ($consPage->getPermission('edit') || $consPage->getPermission('view')) {
                $top->startElement("li");
                $top->addTextElement("a", "<i class='icon-pencil'></i>&nbsp;Consent..", array('href' => "/add-ci/index.php/patient/consent?pid=$pid"));
                $top->endElement("li");
            }
        }
        if ($patMod->getPage('purge') && $patMod->getPage('purge')->getPermission('purge')) {
            $top->addTextElement("a", "<i class='icon-fire'></i>&nbsp;Purge", array("href" => "javascript:;", "onclick" => "purgePatient($pid);", "cle-nong"=>"", "class" => "btn btn-small"));
        }
        if ($person['is_deleted']) {
            $top->endElement("span");
        }
		$top->endElement("div");
    }
    function fieldFunctionPatientGet($fieldName) {
        $patient = CLE::db('patient')->getPatient(CLE::sys()->getPatientId());
        if(!$patient) {
            throw new \ADD_PatientExistsException("Patient not found");
        }
        return Util::val($fieldName,$patient);
    }
}
