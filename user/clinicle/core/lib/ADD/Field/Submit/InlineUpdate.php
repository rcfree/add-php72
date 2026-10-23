<?php



class ADD_Field_Submit_InlineUpdate extends ADD_Field_Submit {

    function submitValues($values, $page) {
        $add = ADD::sys();
        $adb = ADD::db('audit');
        $edb = ADD::db('encounter');
        $form = $page->getComponent('Form');
        $table = $form->getField('table');
        $rowEncs = $table->getRowEncounters($page);
        $recordIds = isset($values['record_id']) ? $values['record_id'] : array();
        if (!is_array($recordIds))
            $recordIds = array($recordIds);

        $recordsById = array();
        $rcounter = 0;
        $counter = 0;

        $newEncs = array();
		$encForm = $edb->getEncounterFormWithType($page->getSetting('encounter'));
        $ofs = $edb->getObservationFieldsInEncounterForm($encForm['id']);
		$recordsById = array();
        foreach ($rowEncs as $id => $rowEnc) {
            $rowEnc->retrieve();
            
            foreach ($ofs as $of) {
                $ofName = $of['name'];
				if (isset($values[$ofName][$id])) {
                    $rowEnc->setObsValue($ofName, $values[$ofName][$id]);
                }
            }
            $rowEnc->store();
        }

        $page->addMessage($page->getSetting('title') . " saved");
    }

    function getLinkTitle($page) {
        return "<i class='icon-ok icon-white'></i>&nbsp;" . $this->getTitle();
    }

    function getLinkClass($page) {
        return "btn btn-primary";
    }

}

?>