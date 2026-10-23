<?php




class ADD_Field_List extends ADD_Field_Base {
    protected $_options = null;
    public function setOptions($options) {
		$this->_options = $options;
    }

    public function getOptions() {
        return $this->_options;
    }

	function finaliseField($page) {
		if(is_object($this->getFieldData("Options"))) {
			$this->_options = $this->getFieldData("Options");
			$this->setFinalised(true);
		}
		$ob = new ADD_Field_OptionBuilder();
		$options = $ob->build($this);
		if(!$options->isInitAfterPopulate()) $options->initOptions($this,$page);
		$this->_options = $options;
		$this->setFinalised(true);
	}
	
	function afterPopulateFields($page) {
		$this->_setHiddenFields($page);
		
		if(!$this->_options) throw new ADD_FieldException("Options not setup in field '".$this->getName()."'");
		
		if($this->_options->isInitAfterPopulate()) $this->_options->initOptions($this,$page);
	}
	
	public function getDisplayText($page,$id) {
		$options = $this->getOptions();
		
		if(!$options) {
			return $this->getValue();
		}
		if($options && !$options->isInit()) $options->initOptions($this,$page);
		$value = $this->getValue();
		if (strlen($value)==0) return "";
                $optLabel = $options->findLabelWithValue($value);
		return $optLabel ? $optLabel : "Option '$value' not present";
	}
	
	function beforeSubmit(&$values, $page) {
		$this->populateValue($values,$page);
		$this->_setHiddenFields($page);
	}
	
	function createPdfElement($rptField,$page,$currentX,$currentY) {
		if ($rptField->getSetting('no_options')) return parent::createPdfElement($rptField,$page,$currentX,$currentY);
		$labelWidth = $this->createPdfLabel($rptField,$page,$currentX,$currentY);	
		$pdf = $page->getPdf();
		$gap = $rptField->getSetting('gap',2);
		$cols = $this->getSetting('columns',$rptField->getSetting('columns',1));
		if ($this->getSetting('inline')) $cols = $this->getOptions()->countOptions();
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		$labelAbove = $rptField->getSetting('label-above',null);
		
		$valueHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		$stack = $rptField->getSetting('stack');
		
		$pdf->SetFont('Arial','',$fontSize);
		$value = $this->getDisplayText($page,null);
		
		$valueWidth = $rptField->getValueWidth($value,$page);
		
		$fill = $rptField->getSetting('no-fill') ? false : true;
		
		$pdf->SetDrawColor(120,120,120);
		$pdf->SetFillColor(255,255,255);
		
		$border = $rptField->getSetting('border',1);
		
		$width = $rptField->calcWidth($labelWidth,$valueWidth,$labelAbove);
		
		 $biggestWidth=0;
		 $options = $this->getOptions();
		 $optCount = $options->countOptions();
		 if ($stack) $cols=1;	
		 if ($optCount>$cols) {
		 	$biggestWidth = $this->calcPdfWidth($rptField,$page);
		 }
		 
			$fieldHeight = $valueHeight;
			$currentY += $lineHeight * 1.8;
			$colX = $currentX;
			$colCount=0;
			$rowCount=0;
			$optsHavingValue=0;
			$valueOnly = $this->getSetting('value-only');
			foreach($options->getOptionList() as $value=>$label) {
				if($value=="") continue;
				$hasValue = $this->hasValue($value);
				if ($stack && !$hasValue) continue;
				$optsHavingValue++;
				if ($colCount>=$cols) {
					$colX = $currentX;
					$fieldHeight+=$valueHeight;
					$colCount=0;
					$currentY += ($lineHeight * 1.55);
					$rowCount++;
				}
				$pdf->SetY($currentY);
				$pdf->SetX($colX+3);
				if (!$stack) $pdf->Rect($colX+3,$currentY+0.5,4,4);
				if ($hasValue && !$stack) {
					$pdf->SetDrawColor(100,100,100);
					$pdf->SetLineWidth(0.6);
					$pdf->Line($colX+3.5,$currentY+2.5,$colX+5,$currentY+4);
					$pdf->Line($colX+5,$currentY+4,$colX+7,$currentY);
					$pdf->SetLineWidth(0.2);
					$pdf->SetDrawColor(120,120,120);
				}
				
				if (!$stack) $pdf->SetX($colX+8);
				
				$pdf->Cell($valueWidth+1,$valueHeight,$valueOnly ? $value : $label,null,1,"L",null);
				$colX = $optCount>$cols ? $colX + $biggestWidth : $colX + 8 + $pdf->getStringWidth($valueOnly ? $value : $label) + $gap;
				$colCount++;
			}
			if ($stack && $optsHavingValue==0 && !$page->isEmpty()) {
				$pdf->SetY($currentY);
				$pdf->SetX($currentX+3);
				$pdf->Cell($valueWidth+1,$valueHeight,"None selected",null,1,"L",null);
			}
			
			$fieldHeight+=$valueHeight;
			$height = $fieldHeight + $gap;
			return array($width,$height);
	}
	function calcPdfWidth($rptField,$page) {
		$biggestWidth = 0;
		$gap = $rptField->getSetting('gap',2);
		foreach($this->getOptions()->getOptionList() as $value=>$label) {
			$width = 8 + $page->getPdf()->GetStringWidth($label);
			if ($width>$biggestWidth) $biggestWidth = $width;
		}
		return $biggestWidth + $gap;
	}
	
	function calcPdfHeight($rptField,$page) {
		if ($rptField->getSetting('no_options')) return parent::calcPdfHeight($rptField,$page);
		
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		$optHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		$gap = $rptField->getSetting('gap',2);
		$cols = $this->getSetting('inline') ?  $this->getOptions()->countOptions() : $this->getSetting('columns',1);
		$labelAbove = $rptField->getSetting('label-above',null);
		
		$colCount = 0;
		$rowCount = 0;
		$valueHeight = ($lineHeight * 1.8);
		
		foreach($this->getOptions()->getOptionList() as $value=>$label) {
			if ($colCount>=$cols) {
				$valueHeight+=$optHeight;
				$colCount=0;
				$rowCount++;
			}
			$colCount++;
		}
		//if ($rowCount==0) {
			$valueHeight+=$optHeight;
		//}
		if ($labelAbove) {
			return $valueHeight + $gap;
		}
		else {
			return $valueHeight + $gap;
		}
	}
	function hasValue($value) {
		if ($value == $this->getValue()) return true;
	}
	function updateExistingData($dbField) {
		$log = ADD::log($this);
		if(!$dbField) throw new ADD_NullValueProvidedException("No database field provided to field '".$this->getName()."'");

		if ($this->getSetting('dynamic')) {
			$log->info("Field '".$this->getName()."' marked as dynamic so not updated");
			return true;
		}
		$options = $this->getOptions();
           
		if (!$options) {
			throw new Exception("No options in field '".$this->getName()."'");
		}

		$valuesCorrect = $this->areListValuesCorrect($dbField, $options, $log);

		if (!$valuesCorrect) {
			throw new ADD_FieldException("Field '".$this->getName()."' not changed into ".$this->getType().". Non-matching options found:".json_encode($options->getOptionList()));
		}
		return $valuesCorrect;
	}

    public function populateOptions($field) {
        
    }

	/**
	 * @param $dbField
	 * @param $options
	 * @param $log
	 * @return bool
	 */
	public function areListValuesCorrect($dbField, $options, $log)
	{
		$edb = ADD::db('encounter');

		$rowCount = 10000;
		$start = 0;

		do {
			$log->info("Check list field values '".$dbField['name']."' ($start,".($start+$rowCount).")");
			$obs = $edb->getObservationsUsingField($dbField, $start, $rowCount);
			if(empty($obs)) {
				break;
			}
			foreach ($obs as $ob) {
				$value = $ob['value'];

				if (is_array($value)) {
					foreach ($value as $key => $label) {
						if ($options->findLabelWithValue($key) === false) {
							$log->warn("Field option '$key' in observation '" . $ob['id'] . "' and encounter '" . $ob['encounter_id'] . "' not defined.");
							return false;
						}
					}
				} else {
					if (strlen($value) == 0) continue;
					if ($options->findLabelWithValue($value) === false) {
						$log->warn("Field option '$value' in obs '" . $ob['id'] . "'/enc '" . $ob['encounter_id'] . "' not defined.");
						return false;
					}
				}
			}
			$start+=$rowCount;
		} while(true);
		return true;
	}

}
?>
