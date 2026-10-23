<?php

class ADD_Field_ModalDictActions extends ADD_Field_Base {
	protected $stored = false;
	function createDrupalElement($page) {}
	function getDisplayText($page,$id) {
		$form = $page->getForm();
		$lo = $page->getLayout();
		$map = $lo->getMap();
		$mapName = $map->get('name');
		$sect = $map->getSectionFromPage($page);
		$sectName = $sect->getName();
		$links = array();
		$actions = $this->getSetting('modal_actions');
		$modal = $this->getSetting('modal_name');
		if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
		if (!$actions) throw new ADD_FieldException("No 'modal_actions' setting provided in field ".$this->getName());
		
		foreach(explode(" ",$actions) as $action) {
			$title = ucfirst($action)." ".$sect->get('single');
			if ($page->getAccessLevel($action)) {
				$links[] = l(
					ucfirst($action)."..",
					"#",
					array("attributes"=>array("onclick"=>"modalEntry('{$action}','{$modal}','{$id}','{$title}',null,null, '{$mapName}','{$sectName}')"),"external"=>true)
				);
			}
		}
		return implode("&nbsp;",$links);
	}
}
?>