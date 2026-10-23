<?php

class ADD_Field_StudyManage extends ADD_Field_Base {
	public function getDisplayText($page,$id) {
		$er = ADD::sys()->getEncounterManager()->recordById($id);
		$er->retrieve();
		$actionType = $er->getObsValue('action_type');
		$encType = "res-action-$actionType";
		$edb = ADD::db('encounter');
		if(!$edb->getEncounterFormWithType($encType)) return "No specific options";
		$em = ADD::sys()->getEncounterManager();
		$cer = $em->child($er,$encType);
		$html = "";
		$actionPage = ADD::sys()->getPageInModule("research","action");
		$actionPage->setSetting('encounter',$encType);
		$actionTitle="Action details..";
		
		$cer->retrieve();
		$actionForm = $actionPage->getComponent('Form');
		$actionForm->reset();
		$obsValues = $cer->getObsValues();
		$html.="<dl>";
		foreach($actionForm->getFields() as $field) {
			$field->populateValue($obsValues,$actionPage);
			$fieldValue = $field->getDisplayText($actionPage,null);
			if(strlen($fieldValue)==0) $fieldValue="-none-";
			$html.="<dt>".$field->getTitle() ."</dt><dd>".$fieldValue."</dd>";
		}
		$html.="</dl>";
		
		$uri = ADD_Util::uri("research/action",array("a"=>$er->getObsValue('action_type'),"pid"=>ADD::sys()->getPatientId(),"eid"=>$id));
		if (!ADD::sys()->hasState('readOnly')) {
			$html.="<a href='$uri' class='btn btn-mini' style='font-decoration:normal'>$actionTitle</a>";
		}
		return $html;
	}
public function getDataType() {
		return null;
	}
	public function getDataLength() {
		return null;
	}
}
?>