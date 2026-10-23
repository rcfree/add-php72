<?php

class ADD_Component_Encounter_Single_Inline extends ADD_Component_Encounter {
    public function initEncounterRecord($idValue=null) {
        $page = $this->_page;
            
        $ppage = $page->getParentPage();
        return $page->getParentPage()->getEncounterRecord($idValue);
    }
    public function submit($values) {
        $page = $this->_page;

            $form = $page->getComponent('Form');

            foreach($form->getFields() as $field) {
                    if (is_subclass_of($field,"ADD_Field_SubmitInterface")) {
                        $field->submitValues($values,$page);
                    }
            }

            return array('redirect'=>$page->createMenuUri());
	}
}
?>