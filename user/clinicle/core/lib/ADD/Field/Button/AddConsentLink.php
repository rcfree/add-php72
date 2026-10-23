<?php

class ADD_Field_Button_AddConsentLink extends ADD_Field_Button {
	protected function getOnClick($form) {
		$modal = $this->getSetting('modal_name');
		if (!$modal) throw new ADD_FieldException("No 'modal_name' setting provided in field ".$this->getName());
		
		$lo = $form->getLayout();
		$map = $lo->getMap();
		$lid = $map->get('name');
		$sect = $map->getSectionFromForm($form);
		$fid = $sect->getName();
		$title = "consent";
		return "modalEntry('create','{$modal}',null,'{$title}',null,null,'{$lid}','{$fid}','/add/api/consent.json');";
	}
	function afterCreateElement(&$element,$page) {
		if ($page->getAccessLevel('create')) parent::afterCreateElement($element,$page);
	}
}
?>