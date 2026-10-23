<?php

class ADD_Field_ModalConsentAction extends ADD_Field_Base {
	protected $stored = false;
	function createDrupalElement($page) {}
	function getDisplayText($page,$id) {
		$form = $page->getForm();
		$lo = $page->getLayout();
		$db = $lo->getDatabase();
		$map = $lo->getMap();
		$sect = $map->getSectionFromPage($page);
		$links = array();
		$action = $this->getSetting('modal_action');
		$modal = $this->getSetting('modal_name');
		//
		$heading = $this->getSetting('single');
		$beforeJs=null;
		$afterJs=null;
		
		//if ($page->getAccessLevel($action)) {
				if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
				$modalField = $form->getField($modal);
				
			//if (!$action) throw new ADD_FieldException("No 'modal_action' setting provided in field ".$this->getName());
				return l(
					$this->getTitle(),
					"#",
					array("attributes"=>array("onclick"=>"modalEntry('{$action}','{$modal}','{$id}','{$heading}',".($beforeJs ? "'{$beforeJs}'" : "null").",".($afterJs ? "'{$afterJs}'" : "null").",'{$lo->getMap()->get('name')}','{$page->getSectionName()}','/add/api/consent.json')"),"external"=>true)
				);
			}
		//};
}
?>