<?php

class ADD_ReportField {
	private $_name = null;
	private $_settings = null;
	private $_type = null;
	private $_title = null;
	private $_level = null;
	private $_fieldData = array();
	private $_value = null;
	
	public function getFieldData($itemName=null) {
		if ($itemName) {
			return isset($this->_fieldData[$itemName]) ? $this->_fieldData[$itemName] : null;
		}
		else {
			return $this->_fieldData;
		}
	}
	function setup($formContainer) { }
	
	function __construct($fieldData) {
		//add fieldData to private value - used by extractLevelAndFieldName
		$this->_fieldData = $fieldData;
		
		//extract field name and outline level (e.g. if text field in container then container level =1, text field level = 2
		list($level,$name) = ADD_Form_Util::extractLevelAndFieldName($this);
		if (!$name) throw new ADD_FieldException("Field name was not supplied in row:".var_export($fieldData,true));
		
		$this->setName($name);
		$this->setLevel($level);
		
		$type = ADD_Util::convertToPascal(ADD_Util::getArrayValue('Type',$fieldData,"text"));
		$this->setType($type);
		$this->setTitle(ADD_Util::getArrayValue('Title',$fieldData));
		
		//less important data is added to settings accessor (e.g. required, minimum, maximum)
		$settings = ADD_Util::getCsvArrayValues('Settings',$fieldData);		
		$this->setSettings($settings);
	}
	
	public function getTop($page,$currentY) {
		$add = ADD::sys();
		$top = $this->getSetting('top');
		if ($top) {
			if (preg_match('/^\+/',$top)) {
				return $page->getCurrentY()+$top;
			}
			elseif (preg_match('/^\-/',$top)) {
				return $page->getCurrentY()-$top;
			}
			else {
				return $top;
			}
		}
		return $currentY;
	}
	public function getLeft($page,$currentX) {
		$left = $this->getSetting('left');
		if ($left) {
			if (preg_match('/^\+/',$left)) {
				return $page->getCurrentX()+$left;
			}
			elseif (preg_match('/^\-/',$left)) {
				return $page->getCurrentX()-$left;
			}
			else {
				return $left;
			}
		}
		return $currentX;
	}
	
	public function createReportElement($report, $currentX, $currentY) {
		if ($this->getSetting('page_break')) {
			$report->addPageBreak();
		}
	}
	
	public function getLineHeight($default) {
		$fontPt = $this->getSetting('size',$default);
		return array($fontPt, 0.352777778 * $fontPt);
	}
	
	protected function truncateText($value, $maxWidth,$page) {
		$pdf = $page->getContent()->getPdf();
		for($i=strlen($value);$i>=0;$i--) {
			$truncString = substr($value,0,$i);
			if ($pdf->GetStringWidth($truncString)<$maxWidth) return $truncString;
		}
		return "";
	}
	public function getName() {
		return $this->_name;
	}
	
	public function setName($name) {
		$this->_name = $name;
	}
	public function getType() {
		return $this->_type;
	}
	public function getValue() {
		return $this->_value;
	}
	public function getTitle() {
		return $this->_title;
	}
	
	public function setTitle($title) {
		$this->_title = $title;
	}
	public function setValue($value) {
		$this->_value = $value;
	}
	public function setSettings($settings) {
		$this->_settings = $settings;
	}
	
	public function getSetting($settingName, $default = null) {
		return isset($this->_settings[$settingName]) ? $this->_settings[$settingName] : $default;
	}
	public function defaultSetting($settingName,$default) {
		if (!isset($this->_settings[$settingName])) $this->_settings[$settingName]=$default;
	}
	public function setSetting($settingName, $value) {
		$this->_settings[$settingName]=$value;
	}
	
	public function getSettings() {
		return $this->_settings;
	}
	public function setLevel($level) {
		$this->_level = $level;
	}
	public function getLevel() {
		return $this->_level;
	}
	public function setType($type) {
		$this->_type = $type;
	}
	public function finaliseField($report) {
	}
	
	function populateValue($values, $report, $clearFirst=false) {
		$fieldName = $this->getName();
		if ($clearFirst) $this->setValue(null);
		if(isset($values[$fieldName])) {
			$this->setValue($values[$fieldName]);
		}
	}
	
	public function removeData($form) {
		$form->unsetData($this->getName());
	}
	
	function beforePopulateFields($report) { }
	function afterPopulateFields($report) { }
	function calcHeight($page) {
		return 0;
	}
	function calcWidth($labelWidth,$valueWidth,$labelAbove) {
		return 0;
	}
	function getFormField() {}
}
?>
