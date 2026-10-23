<?php

class ADD_Field_Submit_AddStandardTests extends ADD_Field_Submit {
	
	function submitValues($values,$page) {
	        if (!$this->isSubmittedField($values)) return;
            $add = ADD::sys();
            $ddb = ADD::db('dictionary');
            $page = $add->getPageFromPim($this->assertSetting("use_page"));
            $ppage = $page->getParentPage();
            $per = $ppage->getEncounterRecord($add->getParam('eid'));
            $encType = $page->getSetting('encounter');

            $dictName = $this->assertSetting('dictionary');
            $addGroups = $this->assertSetting('groups');
            $items = $ddb->getDictionaryItemsInGroups($dictName,explode(";",$addGroups));

            if (!$items) $items = array();

            $em = $add->getEncounterManager();
            $obsMaps = $this->getSetting('obs_map');
            $cobs = array();
            
            if ($obsMaps) {
                foreach($obsMaps as $obsMap) {
                        list($source,$target) = explode(":",$obsMap);
                        $cobs[$target]=$values[$source];
                }
            }
            $targetFld = $this->assertSetting('target_field');
            
            foreach($items as $item) {
                $cer = $em->newChild($per,$encType);
                $cobs[$targetFld]=$item['value'];
                $cer->store($cobs); 
            }
            $page->addMessage($this->assertSetting('add_message')." (".count($items)." records)");
	}

	function getLinkTitle($page) {
		return "<i class='icon-ok'></i>&nbsp;".$this->getTitle();
	}
	
}

?>