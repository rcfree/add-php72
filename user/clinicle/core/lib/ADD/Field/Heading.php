<?php

class ADD_Field_Heading extends ADD_Field_Base {
	protected $drupalElementType='item';
	protected $stored = false;
	function createDrupalElement($page) {
		$element = parent::createDrupalElement($page);
		$level = $this->getSetting('level');
		$align = $this->getSetting('align');
		if (!$align) $align="center";
		if (!$level) $level="3";
		$element['#markup']="<div style=font-weight:bold;color:darkgreen;text-decoration:underline name='".$this->getName()."'>".$element['#title']."</div>";
		unset($element['#title']);
		return $element;
	}
}
?>