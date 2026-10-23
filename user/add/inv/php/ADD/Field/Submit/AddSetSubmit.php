<?php
class ADD_Field_Submit_AddSetSubmit extends ADD_Field_Submit {
	
	function submitValues($values,$page) {
		if (!$this->isSubmittedField($values)) return;
        $add = ADD::sys();

        $usePage = $add->getPageFromPim($this->assertSetting("use_page"));
		$form = $page->getComponent('Form');
		$db = ADD::db('encounter');
		$adb = ADD::db('audit');
		$ddb = ADD::db('dictionary');
		
        list($grpSource,$grpTarget) = explode(":",$this->assertSetting('obs_group_map'));
        $dictField = $form->getField($grpSource);
        $dictName = $dictField->assertSetting('dictionary_groups');
                
		$obsMaps = $this->getSetting('obs_map');
		if ($obsMaps) {
                    $obs = array();
                    foreach($obsMaps as $obsMap) {
                            list($source,$target) = explode(":",$obsMap);
                            $obs[$target]=$values[$source];
                    }
                }
		
		$groupName = ADD_Util::val($grpSource,$values);
		if (empty($groupName)) throw new ADD_FieldException("Please select a group to add a set");
		$items = $ddb->getDictionaryItemsInGroups($dictName, array($groupName));
		if (!$items) {
                    $page->addWarning("There are no items in the '".ADD_Util::nameToLabel($groupName)."' group");
                    return;
		}
		$ppage = $page->getParentPage();
		$per = $ppage->getEncounterRecord($add->getParam('eid'));
               $em = ADD::sys()->getEncounterManager();
		foreach($items as $item) {
                    $obs[$grpTarget]=$item['value'];
                    $er = $em->newChild($per,$usePage->getSetting('encounter'));
                    $er->store($obs);
		}
		$page->addMessage("Successfully added '".ADD_Util::nameToLabel($groupName)."' group (".count($items)." results)");
	}

	function getLinkTitle($page) {
		return "<i class='icon-ok'></i>&nbsp;".$this->getTitle();
	}
	
}

?>