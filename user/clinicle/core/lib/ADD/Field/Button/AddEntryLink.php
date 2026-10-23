<?php

class ADD_Field_Button_AddEntryLink extends ADD_Field_Button {
	protected function getOnClick($page) {
		if(ADD::sys()->hasState("readOnly")) return;
		//$penc = $form->getParentEncounter();
		$modal = $this->getSetting('modal_name');
		if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
		
		$form = $page->getComponent('Form');
		
		$links = array();
		$single = $page->getSetting('single');
		$mapName=$page->getModule()->getName();
		$sectName = $page->getName();

		$modalEntryOpt = array(
			"action" => "create",
			"name" => $modal,
			"elementId" => $modal,
			"description" => $single,
			"lid"=>$page->getModule()->getName(),
			"fid"=>$page->getName(),
                        "pid"=>ADD::sys()->getPatientId()
		);
		$json = json_encode($modalEntryOpt);
		$json = str_replace('"',"'",$json);
		return "modalEntry($json);";
	}
	
	protected function getLinkTitle($page) {
		return "<i class='icon-plus'></i>&nbsp;".$this->getTitle();
	}
}
?>