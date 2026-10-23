<?php

class ADD_Component_AutoSectionCreate extends ADD_Component {
	function submit($values) {
		$web = ADD::web();
		$form = $this->_page->getComponent('Form');
		$primaryConditions = $values['primary_condition'];
		$sectionMap = array(
			1=>"blness-exacerb",
			2=>"blness-exacerb",
			3=>"blness-hfail",
			4=>"blness-cap"
		);
		$eid = ADD::sys()->getParam('eid');
		$pid = ADD::sys()->getParam('pid');
		$epiRepo = ADD::sys()->getService(\ClinicLE\Episode\Resource\EpisodeServices::EPISODE_REPOSITORY);
		$episode = $epiRepo->getEpisodeWithSectionId($eid);
		if(!$episode) {
			ADD::debug("no episode found");	
			return;
		}
		$em = ADD::sys()->getEncounterManager();
			
		foreach($primaryConditions as $key=>$value) {
			$sectionName = ADD_Util::val($key,$sectionMap);
			if($epiRepo->doesEpisodeHaveSection($sectionName, $episode['id'])) {
				return;
			}
			$sr = $em->newSection($pid,$sectionName);
			$sr->setEncData("episode_id",$episode['id']);
			$sr->store();
			$web->addMessage("Automatically added selected diagnosis section to episode");
		}
		
	}
}