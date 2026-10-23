<?php

class ADD_Field_List_Checkboxes extends ADD_Field_List_MultiSelect {
	public function getDisplayText($form, $id) {
		$options = $this -> getOptions();
		$keyedValues = $this -> getValue();
		$output = array();
		
		if (!$keyedValues) {
            return $this->getSetting("blank_no_options") ? "" : "- No options selected -";
		}
		if (!is_array($keyedValues)) {
			$keyedValues = ADD_Util::unserialize($keyedValues);
			if($keyedValues && !is_array($keyedValues)) {
				$form -> addWarning("Possible issue with checkbox data in field '" . $this -> getName() . "'");
				return "ERROR WITH DATA";
			}
			$this->setValue($keyedValues);
		}
		foreach ($keyedValues as $value => $label) {
			if (empty($label))
				continue;
			$valueWithKey = $options -> findLabelWithValue($value);
			$output[] = $valueWithKey ? $valueWithKey : "Option '$value' not present";
		};
		if (count($output) == 0) {
			return "- Nothing -";
		} else {
			return implode(", ", $output);
		}
	}

	function addValue($newValue) {
		return $this -> setCheckbox($newValue, true);
	}

	function removeValue($newValue) {
		return $this -> setCheckbox($newValue, false);
	}

	private function setCheckbox($obsName, $isChecked) {
		$newValue = $isChecked ? 1 : null;
		$currentValue = $this -> getValue();
		$options = $this -> getOptions();
		if (!isset($options[$obsName]))
			throw new ADD_FieldException("Toggle option '$newValue' supplied to checkbox '" . $this -> getName() . "' not defined");
		if (!$currentValue)
			$currentValue = array();
		if (isset($currentValue[$obsName]) && $currentValue[$obsName] == $newValue)
			return false;
		if (!isset($currentValue[$obsName]) && !$isChecked)
			return false;
		$currentValue[$obsName] = $newValue;
		$this -> setValue($currentValue);
		return true;
	}

	function beforeSubmit(&$values, $page) {
		if ($this -> getLevel() == "DEL")
			return;
		parent::beforeSubmit($values, $page);
		if (isset($values[$this -> getName()]))
			return;
		$values[$this -> getName()] = array();
	}

	protected function _createControl($page, $html, $value, $label, $checkClass) {
		$attrs = array("name" => $this -> getName() . "[$value]", "type" => "checkbox", "value" => $value, "autocomplete" => "no", );
		$html -> startElement("label", array("class" => $checkClass));
		if ($this -> hasValue($value))
			$attrs['checked'] = "checked";
		if (ADD::sys() -> hasState('readOnly') || $this->getSetting('readonly')) {
            $hiddenAttrs = $attrs;
            $hiddenAttrs['style']='display:none';
			$html -> addElement("input", $hiddenAttrs);
            $attrs['disabled'] = 'disabled';
        }
		$html -> addElement("input", $attrs);
		$html -> addHtml($label);
		$html -> endElement("label");
	}

	function addMarkup($page) {
		$add = ADD::sys();
		$html = $page -> html();

		$html -> addTextElement("label", $this -> getTitle(), array("class" => "control-label"), $this -> getName() . "_label");
		$checkClass = "checkbox";
		if ($this -> getSetting('inline'))
			$checkClass .= " inline";
		$cols = $this -> getSetting('columns');
		if ($cols >= 1) {
			$html -> startElement("table", array("class" => "table table-condensed table-striped"));
			$html -> startElement("tr");
		}
		$rowCount = 0;
		foreach ($this->getOptions()->getOptionList() as $value => $label) {
			if ($rowCount >= $cols) {
				$html -> endElement("tr");
				$html -> startElement("tr");
				$rowCount = 0;
			}
			if ($cols >= 1)
				$html -> startElement("td");
			$this -> _createControl($page, $html, $value, $label, $checkClass);
			if ($cols > 1)
				$html -> endElement("td");
			$rowCount++;
		}
		$html -> endElement("tr");
		$html -> endElement("table");
	}

}
?>
