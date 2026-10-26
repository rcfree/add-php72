<?php


class ADD_ReportField_Obs extends ADD_ReportField {
	protected $_field = null;
        private static $_obsFields=array();
        
        public function getFieldFromEncounterForm($encType,$fieldName) {
            $edb = ADD::db('encounter');
		
            if (!isset(self::$_obsFields[$encType])) {
                $encForm = $edb->getEncounterFormWithType($encType);
                self::$_obsFields[$encType]=$edb->getObservationFieldsByIdAndName($encForm);
            }
            $obsFields = self::$_obsFields[$encType];
            
            return ADD_Util::val($fieldName,$obsFields);
        }
        
	public function finaliseField($report) {
		$add = ADD::sys();
		$rsect = $report->getReportConfig()->getReportSection();
		
		$fieldBits = explode(".",$this->getName());
		$mod = $add->getModule($add->getParam('module'));
        $ff = new ADD_FieldFactory_Database();

        $dbField = null;
        if (count($fieldBits)==1) {
			$page = $mod->getPage("medhistory");
			$dbField = $this->getFieldFromEncounterForm($report->getEncounterType(),$this->getName());
			if (!$dbField) {
				ADD::log($this)->warn("Report observation '".$this->getName()."' is not defined in encounter type '".$report->getEncounterType()->getType()."'; skipping");
				return;
			}
		}
		else {
            if ($fieldBits[0]=='parent') {

                    $page = $mod->getPage($fieldBits[1]);
                    $dbField = $this->getFieldFromEncounterForm($page->getSetting('encounter'),$fieldBits[2]);

                    if (!$dbField) {
                        ADD::log($this)->warn("Report observation '{$fieldBits[2]}' is not defined in encounter type '{$page->getSetting('encounter')}'; skipping");
                        return;
                    }

            }
            else {
                $page = $mod->getPage($fieldBits[0]);

                if (!$page) throw new ADD_ReportException("Observation page '".$fieldBits[0]."' does not exist");
                $dbField = $this->getFieldFromEncounterForm($page->getSetting('encounter'),$fieldBits[1]);
                if (!$dbField) {
                    ADD::log($this)->warn("Report observation '{$fieldBits[1]}' is not defined in encounter type '{$page->getSetting('encounter')}'; skipping");
                    return;
                }

            }
        }
        $field = $ff->newInstance($dbField);
        $field->finaliseField($page);
        if (!$field) throw new ADD_ReportException("Report Obs field '".$this->getName()."' could not be instantiated");
        $field->setTitle(strip_tags($field->getTitle()));
        $this->_field = $field;
        
	}
	
	public function createReportElement($page,$currentX,$currentY) {
		if (!$this->_field) {
			return array(0, 0, $currentX, $currentY);
		}
		$pdf = $page->getPdf();
		$gap = $this->getSetting('gap',2);
		$currentX = $this->getLeft($page,$currentX);
		$currentY = $this->getTop($page,$currentY);
		if (!$this->_field) throw new ADD_ReportException("Report Obs field '".$this->getName()."' could not be instantiated");
		$settings = $this->getSettings();
		if ($settings) {
			foreach($settings as $key => $value) {
				$this->_field->setSetting($key,$value);
			}
		}
		list($width,$height) = $this->_field->createPdfElement($this,$page,$currentX,$currentY);
		$width = (float) $width;
		$height = (float) $height;
	
		return array($width, $height, $currentX+$width, $currentY+$height);
	}
	
	function calcHeight($page) {
		return $this->_field ? $this->_field->calcPdfHeight($this,$page) : 0;
	}
	
	function getValueWidth($value,$page) {
		if (!$this->_field) {
			return 0;
		}
		$pdf = $page->getPdf();
		$valueWidth = 0;
		if ($page->isEmpty() || strlen($value)==0) {
			if ($this->_field->getSetting('size')) {
				$valueWidth = $this->_field->getSetting('size');
			}
			else {
				$valueWidth="20";
			}
		}
		else {
			if ($this->getSetting('length')) {
				$valueWidth = $this->getSetting('length');
			}
			else {
				$valueWidth = $this->_field->getSetting('size',$pdf->GetStringWidth($value))+0.5;
			}
		}
		return $valueWidth;
	}
	function calcWidth($labelWidth,$valueWidth,$labelAbove) {
		if ($labelAbove) {
			return $labelWidth > $valueWidth ? $labelWidth : $valueWidth;
		}
		else {
			return $labelWidth + $valueWidth;
		}
	}
	function populateValue($values, $page, $ignoreNew=false) {
		if (!$this->_field) {
			return;
		}
		$fieldName = $this->getName();
		
		if(isset($values[$fieldName])) {
			$this->_field->setValue($values[$fieldName]);
		}
	}
	
	function beforePopulateFields($report) {
		if ($this->_field) {
			$this->_field->beforePopulateFields($report);
		}
	}
	function afterPopulateField($report) {
		if ($this->_field) {
			$this->_field->afterPopulateFields($report);
		}
	}
	function getFormField() {
		return $this->_field;
	}
}
?>