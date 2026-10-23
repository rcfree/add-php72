<?php

class ADD_Component_RequiredFields_Notify extends ADD_Component_RequiredFields {
    const COMPLETE_ICON = "fa fa-check-square-o black-text";
    const INCOMPLETE_ICON = "fa fa-square-o black-text";
	function afterGenerate() {
		$add = ADD::sys();
        $page = $this->_page;
		if ($add->hasState(ADD_System::READONLY)) return;
		$html = $page->html();
		foreach($this->getRequiredFields($page) as $field) {
			$name = $field->getName();
			$currLabel = $html->get($name."_label");
			$currLabel['text']=$currLabel['text']."<span class='required'>*</span>";
			$html->set($name."_label",$currLabel);
		}
		if ($add->hasState('new')) return;
        
		$er = $page->getEncounterRecord();
        $er->retrieve();
		
		        
            $form = $page->getComponent('Form');
            $fieldList = array();
            foreach($this->findIncompleteFields($er->getObsValues(),$page) as $fieldName) {
                    $field = $form->getField($fieldName);
                    $inc = $html->get($fieldName."_start");
                    $inc['attrs']['class']="field control-group warning content-".$fieldName;

                    $html->set($fieldName."_start",$inc);
                    $fieldList[]=$fieldName;

            }
            if (count($fieldList)>0) $page->addJavascript("ADD.assign('NotifyRequired',".json_encode($fieldList).")");
	}

        function menuIcon(&$menuIcon) {
            $page = $this->_page;
            if ($page->getSetting('no-menu')) return;
            $er = $page->getEncounterRecord();
            $er->retrieve();
            if ($er->exists() && $er->getEncData('is_complete')==1) {
                $menuIcon[] = self::COMPLETE_ICON;
            }
            else {
                $menuIcon[] = self::INCOMPLETE_ICON;
            }

        }
}
?>