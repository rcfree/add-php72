<?php

class ADD_ReportField_Heading extends ADD_ReportField {
	protected $drupalElementType='item';
	protected $stored = false;
	function createDrupalElement($page) {
		$element = parent::createDrupalElement($page);
		$level = $this->getSetting('level');
		if (!$level) $level="3";
		$attrs = $this->getAttributes();
		$style = "";
		if (isset($attrs['style'])) $style = $attrs['style'];
		$element['#markup']="<h$level style='{$style}'>".$element['#title']."</h$level>";
		unset($element['#title']);
		return $element;
	}
}
?>