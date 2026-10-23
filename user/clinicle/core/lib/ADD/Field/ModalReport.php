<?php

class ADD_Field_ModalReport extends ADD_Field_Base {
	protected $stored = false;
	function createDrupalElement($page) {}
	function getDisplayText($page,$id) {
		$lo = $page->getLayout();
		$map = $lo->getMap();
		$pid = $lo->getPatientId();
		
		$opts = array('query'=>array('name'=>$this->getSetting('report'),'id'=>$id,'pid'=>$pid,'lo'=>$map->get('name')));
		return l(
					$this->getTitle(),
					"report",
					$opts				
		);
	}
}
?>