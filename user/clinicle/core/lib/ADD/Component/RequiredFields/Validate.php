<?php

class ADD_Component_RequiredFields_Validate extends ADD_Component_RequiredFields {
	function afterGenerate() {
        $page = $this->_page;
		$html = $page->html();
		
		$reqFields = array();
		
		foreach($this->getRequiredFields() as $field) {
			$name = $field->getName();
			$currLabel = $html->get($name."_label");
			$currLabel['text']=$currLabel['text']."<span class='required' style='color:red'>*</span>";
			
			$html->set($name."_label",$currLabel);
			$inc = $html->get($name."_start");
			$inc['attrs']['class']="control-group content-".$name;
			$html->set($name."_start",$inc);
			$reqFields[]=$name;
		}
		$contComp = $page->hasComponent('Content');
		if (!$contComp || ($contComp && get_class($page->getComponent('Content'))!="ADD_Component_Content_ModalEntry")) {
			$page->addJavascript("ADD.assign('ErrorRequired',".json_encode($reqFields).")");
		}
	}
        
        function beforeSubmit(&$values) {
            $invalidFields = array();
            foreach($this->getRequiredFields() as $field) {
                $name = $field->getName();
                if (!isset($values[$name])) $invalidFields[]=$field->getTitle();
            }
            if (count($invalidFields)==0) return;
            throw new ADD_ComponentException("The following required ".ADD_Util::pluralize($invalidFields,"field")." must be filled in: '".implode("','",$invalidFields)."'");
        }
}
?>