<?php
use ClinicLE\System\Service\CoreServices;
class ADD_Component_PreventComorbDuplicate extends ADD_Component {
	public function beforeSubmit(&$values) {
		$page = $this->_page;
		$code = ADD_Util::val('icd10_code',$values);
		$form = $page->getComponent('Form');
		$parentId = ADD_Util::val('__parent',$values,$form->getUncleanValue("__parent"));
		
		$repo = ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY);
		$query = $repo->createQuery();
		$query->filterByType($page->getSetting('encounter'));
		$query->filterBySectionId($parentId);
		$query->filterByEncounterField("is_deleted",1,"!=");
		$query->filterByObservationField("icd10_code",$code);
		
		
		$runner = ADD::sys()->getService(CoreServices::QUERY_RUNNER);
		$count = $runner->countRows($query);
		if($count==0) return;
		throw new Exception("Sorry you cannot add this diagnosis because it is already present");
	}
}

