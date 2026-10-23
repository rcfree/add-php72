<?php

class ADD_Field_ModalActions extends ADD_Field_Base {
	protected $stored = false;
	function createDrupalElement($page) {}
	function getDisplayText($page,$id) {
		$form = $page->getComponent('Form');
		
		$links = array();
		$actions = $this->getSetting('modal_actions');
		$modal = $this->getSetting('modal_name');
		if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
		if (!$actions) throw new ADD_FieldException("No 'modal_actions' setting provided in field ".$this->getName());
		
		$hr = ADD::web()->newContent();
		foreach(explode(" ",$actions) as $action) {
			$single = $page->getSetting('single');
			$mapName=$page->getModule()->getName();
			$sectName = $page->getName();
			if ($page->getPermission($action)) {
				$modalEntryOpt = array(
					"action" => $action,
					"name" => $modal,
					"elementId" => $modal,
					"id" => $id,
					"heading" => $single,
					"lid"=>$page->getModule()->getName(),
					"fid"=>$page->getName(),
                                    "pid"=>ADD::sys()->getPatientId(),
				);
				$json = json_encode($modalEntryOpt);
				$json = str_replace('"',"'",$json);
				$hr->addTextElement("a",ucfirst($action)."..",array("href"=>"javascript:;", "onclick"=>"modalEntry($json)")); 
					
				$hr->addHtml("&nbsp;");
			}
		}
		return $hr->toHtml();
	}
}
?>