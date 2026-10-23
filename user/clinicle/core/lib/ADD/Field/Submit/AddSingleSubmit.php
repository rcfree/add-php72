<?php



class ADD_Field_Submit_AddSingleSubmit extends ADD_Field_Submit {

    function submitValues($values, $page) {
        if (!$this->isSubmittedField($values)) return;
        $add = ADD::sys();
        $form = $page->getComponent('Form');
        $ddb = ADD::db('dictionary');
        $obsMaps = $this->getSetting('obs_map');
        $obs = array();
        $testVal = ADD_Util::val('single_test', $values);
        $testTitle = $form->getUncleanValue('__single_test_ac');
        $dictName = $this->assertSetting('dictionary');
        if (ADD_Util::isEmpty($testVal) && strlen($testTitle) > 0) {
            $di = $ddb->getDictionaryItemByTitle($dictName, $testTitle);
            if ($di) {
                $testTitle = ucfirst($di['title']);
                $page->addWarning(ucfirst($page->getSetting('test_single')) . " with name '" . $testTitle . "' already exists. Not created");
            } else {
                $testTitle = ucfirst($testTitle);
                $page->addMessage("Created new " . $page->getSetting('test_single') . " '$testTitle'");
                $di = $ddb->storeDictionaryItem($dictName, array('title' => $testTitle));
            }
                
            $values['single_test'] = $di['value'];
        } else {
            $dict = $ddb->getDictionaryByName($dictName);
            $di = $ddb->getDictionaryItemWithValue($dict, $testVal);
            $testTitle = $di['title'];
        }
        foreach ($obsMaps as $obsMap) {
            list($source, $target) = explode(":", $obsMap);
            $obs[$target] = $values[$source];
        }

        $ppage = $page->getParentPage();
        $encType = $page->assertSetting('encounter');
        $per = $ppage->getEncounterRecord();
        $em = $add->getEncounterManager();
        $er = $em->newChild($per, $encType);
        $er->store($obs);
        $page->addMessage("Successfully added '$testTitle' " . $page->getSetting('single'));
    }

    function getLinkTitle($page) {
        return "<i class='icon-ok'></i>&nbsp;" . $this->getTitle();
    }

    function getButtonType($page) {
        return "button";
    }

}

?>