<?php
use Clinicle\Util\Redirector;
class ADD_Component_Encounter_EpisodeDomain extends ADD_Component_Encounter_Domain_ManualEnrol {
	public function menu($options) {
        $page = $this->_page;
		$add = ADD::sys();
        $pid = $add->getPatientId();
        if (ADD_Util::isEmpty($pid)) {
			return;
		}
		
		if(!$add->getCurrentPage()) throw new Exception("Current page not found");
        if ($add->getCurrentPage()->hasComponent('RemovePatient'))
            return;

        $patient = ADD::db('patient')->getPatient($pid);
		 if (!$patient || $patient['is_deleted']) {
			return;
		}
		if(isset($patient['deceased']) && $patient['deceased']) $this->setDeceased(true);
		$db = ADD::db('BASE');
		
		$epiRepo = ADD::sys()->getService(\ClinicLE\Episode\Resource\EpisodeServices::EPISODE_GROUP);

        $canAdd = $this->canAddToDomain();

        if ($epiRepo->hasNoEpisodes($page->getSetting('episode'),$add->getPatientId())) {
			if (!$canAdd) {
                return;
            }
            else {
                $this->_addDomainTitle($options);
                $this->_startDomainOptions($options);
                $this->menuDomainAddOption($options);
                $options->endElement("ul");
                $this->_endDomainOptions($options);
                return;
            }
		}

        $domainOptions = ADD::web()->newContent();
        $this->_addDomainOptions($domainOptions);
        if($domainOptions->countElements()==0) {
            $options = null;
            return;
        }
        $this->_addDomainTitle($options);

        $this->_startDomainOptions($options);
        $options->startElement("ul",array("class"=>"nav nav-list"));

        $options->addHtml($domainOptions);
        if ($canAdd) {
            $this->menuDomainAddOption($options);
        }
		$options->endElement("ul");
        $this->_endDomainOptions($options);
    }
		
	function generate() {
		$add = ADD::sys();
		$db = ADD::db('BASE');
		$page = $this->getPage();
		$pid = $add->getPatientId();
		$a = $add->getParam('a');
				
		if (!ADD::db('token')->register()) {
			$page -> addError("You have just tried to ".$a." a '" . $page -> getSetting('title') . "' episode. To ensure data integrity you can only ".$a." one at a time.");
			return;
		}

		if($a=="delete") {
			$epid = $add->getParam('epid');
			if(!$epid) {
				throw new Exception("Cannot delete episode. An ID was not supplied");
			}
			$db->update("episode",$epid,array("is_deleted"=>1));
			$sections = $db->fetchAll("select id from section where episode_id=?",$epid);
			foreach($sections as $section) {
			    $db->execute("update encounter set is_deleted=1 where parent_encounter_id=?",array($section['id']));
			    $db->update("section",array("id"=>$section['id']),array("is_deleted"=>1));
            }
			ADD::web()->addMessage("Deleted ".$page->getSetting('title')." episode");
			return;
		}
		$epiRepo = ADD::sys()->getService(\ClinicLE\Episode\Resource\EpisodeServices::EPISODE_GROUP);
		$episodeType = $epiRepo->getEpisodeType($page->getSetting('episode'));
		$episodeTypeId = $episodeType['id'];
		if(!$episodeTypeId) {
			throw new Exception("Episode type '".$page->getSetting('episode')."' not found");
		}
		$episode = $epiRepo->addEpisode($episodeTypeId,$pid);
		$createSecondaries = $page->getSettingList('episode-add-sections', ',');
		
		$ids = array();
        if (count($createSecondaries) == 0) return;
		
        $module = $page->getModule();
        foreach ($createSecondaries as $createSecondary) {
            $spage = $module->getPage($createSecondary);
            if (!$spage)
                throw new ADD_PageException("Page '" . $createSecondary . "' not found in module");
            $encType = $spage->getSetting('encounter');
            $cer = ADD::sys()->getEncounterManager()->newSection($pid,$encType);
			$cer->setEncData("episode_id",$episode['id']);
            $cer->store();
			$ids[]=$cer->getId();
         }
		ADD::web()->addMessage("Added new ".$page->getSetting('title')." episode");
		$redirector = new Redirector();
		$redirector->redirectUsingSetting($page,$pid,$cer->getId(),$page->getSetting('title')." episode");
	}

	protected function _addDomainOptions($options, $canCreate = false) {
		$db = ADD::db('BASE');
		$add = ADD::sys();
		$page = $this->getPage();
		$params = array($page->getSetting('episode'),$add->getPatientId());
		$episodes = $db->fetchAll("select e.* from episode e left join episode_type et on e.episode_type_id=et.id where et.name=? and e.patient_id=? and e.is_deleted!=1",$params);
		$ecount = 1;

		foreach($episodes as $episode) {
            $episodeOption = $this->menuEpisodeOptionCreate($episode,$ecount);
            if($episodeOption) {
                $this->menuEpisodeStart($options);
                $options->addHtml($episodeOption);
                $this->menuEpisodeEnd($options);
                $options->addTextElement("li", "", array("class" => "divider"));
            }
			$ecount++;
			
		}


	}
	
	function menuEpisodeStart($episodeHtml) {
		
	}
	function menuEpisodeEnd($episodeHtml) {
		
	}
	function menuEpisodeOptionCreate($episode,$ecount) {
		$episodeHtml = ADD::web()->newContent();
		$this->setCurrentEpisode($episode);


        $episodeItems = $this->menuEpisodeOptionItems($episode);
        if(!$episodeItems) {
            return null;
        }
        $this->menuEpisodeOptionStart($episodeHtml,$episode,$ecount);
        $episodeHtml->addHtml($episodeItems);
        $this->menuEpisodeOptionEnd($episodeHtml,$episode);
		return $episodeHtml;
	}
	function menuEpisodeOptionStart($episodeHtml,$episode,$ecount) {
		$page = $this->getPage();
		$episodeHtml->startElement("li",array("class"=>"nav-header"));
		$episodeHtml->addHtml($page->getSetting('episode-title')." ".$ecount);
		$episodeHtml->endElement("li");
	}
	function menuEpisodeOptionItems($episode) {
        $episodeItems = ADD::web()->newContent();
		$page = $this->getPage();
        $hasOptions = false;
		foreach($page->getChildPages() as $cpage) {
			$html = $cpage->createMenu();
            if($html->countElements()>0) {

                $episodeItems->addHtml($html);
                $hasOptions = true;
            }
            else {
                $episodeItems->addTextElement("p","No access to ".$page->getSetting('title')." visit data",array("class"=>"text-warning"));
            }
		}

        if($hasOptions) {
		    return $episodeItems;
        }
        return null;
	}
	function menuEpisodeOptionEnd($episodeHtml,$episode) {
		if(!$this->_page->getPermission("delete")) return;
		$menuParams = array();
		$this->menuParams($menuParams);
		$menuParams['a']="delete";
		$menuParams['epid']=$episode['id'];
		$menuPath="";
		$this->menuPath($menuPath);
		//$episodeHtml->startElement("li",array("class"=>"episode-button"));
		$episodeHtml->addTextElement("li","<i class='icon-minus-sign icon-white'></i>&nbsp;Delete episode..",array("ng-click"=>"gotoPage('".ADD_Util::uri($menuPath,$menuParams)."')","class"=>"btn btn-warning btn-small 
        episode-button"));
		//$episodeHtml->endElement("li");
	}
	public function getCurrentEpisode() {
		return $this->episode;
	}
	public function setCurrentEpisode($episode) {
		$this->episode = $episode;
	}
	function expectedPermissions(&$expPerms) {
		parent::expectedPermissions($expPerms);
        $expPerms[]='delete';
    }
	function menuDomainAddOption($options) {
		$page = $this->_page;

        $href = $page->createMenuUri();
		//$options->startElement("li",array("class"=>""));
		
		$options->addTextElement('li', "<i class='icon-plus-sign icon-white'></i>&nbsp;" . $page->getSetting('create-label', "Add patient to " . $page->getSetting('title') . ".."), array('ng-click' => "gotoPage('$href')","class"=>"btn-small btn btn-info episode-button"));
		//$options->endElement("li");
	}
		private $episode;
}