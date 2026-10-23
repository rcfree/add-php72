<?php

class ADD_ReportField_ObsBlock extends ADD_ReportField {
	private $_typeMap = array(
		'Fieldset' => 'RBox',
		'HorizContainer' => 'HContainer',
		'Tab' => 'RBox',
		'ConceptGroup'=>'ObsBlock'
	);
	
	public function setup($report) {
		list($pageName,$fieldName,$form) = $this->determineObsBlockForm();
		$level = $this->getLevel();
        $ff = new ADD_FieldFactory_ReportData();
		
		if ($fieldName) {
			$field = $form->getField($fieldName);
			if ($field) {
				if (!$this->getTitle()) $this->setTitle($field->getSetting('display_title',$field->getTitle()));
				$this->addChildFieldsToReport($pageName, $field,$form,$report,$level);
			}
			else {
				throw new ADD_ReportException("Field '$fieldName' not found in page '$pageName' in obs block ".$this->getName());
			}
		}
		else {
			$tlFieldNames = $form->getTopLevelFields();
			foreach($tlFieldNames as $tlFieldName) {
				$tlField = $form->getField($tlFieldName);
				if (is_subclass_of($tlField,"ADD_Field_Container")) {
					$rptFieldData = array(
						"Level" => $level,
						"Type" => $this->_typeMap[$tlField->getType()],
						"Title" => $tlField->getTitle(),
						"Field" => $pageName.".".$tlField->getName(),
						"Settings" => $tlField->getSettings(),
					);
					
				}
				else {
					$rptFieldData = array(
						"Level" => $level,
						"Type" => "Obs",
						"Field" => $pageName.".".$tlField->getName()
					);
				}
			
				$rptField = $ff->newInstance($rptFieldData);
				$report->addField($rptField);
				$this->addChildFieldsToReport($pageName, $tlField, $form,$report,$level);
			}
		}
	}
	function writeText($text,&$currentX,&$currentY,$report) {
		$pdf = $report->getPdf();
		$font = $this->getSetting('font',"Arial");
		$fontDec = $this->getSetting('decoration','B');
		$gap = $this->getSetting('gap',2);
		$align = $this->getSetting('align','L');
		$width = $this->getSetting('width');
		$border = $this->getSetting('border','0');
		
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		if (strlen($text)==0) return;
		$pdf->SetFont($font,$fontDec,$fontSize);
		
		$pdf->SetXY($currentX,$currentY);
		
		$currentY += $lineHeight + $gap;
		
		if (!$text) $value=" ";
		$textWidth = $pdf->GetStringWidth($text);
		if ($width) {
			$pdf->Cell($width,$currentY,$text,$border,0,$align);
		}
		else {
			$pdf->Write($lineHeight * 1.2,$text);
		}
	}
	
	function createReportElement($report, $currentX, $currentY) {
		$pdf = $report->getPdf();
		$currentX = $this->getLeft($report,$currentX);
		$currentY = $this->getTop($report,$currentY);
		
		$children = $report->getChildFields($this->getName());
		$childrenHeight = 0;
		$childrenWidth = 0;
		
		$value = $this->getTitle();
		$prevY = $currentY;
		if ($this->getTitle()) {
			list($fontSize, $lineHeight) = $this->getLineHeight(10);
			$gap = $this->getSetting('gap',2);
		
			$heightOfFirst = isset($children[0]) ? $children[0]->calcHeight($report) : 0;
			$isPageAdded = $report->getContent()->checkPageBreak($report,($lineHeight + $gap + $heightOfFirst), $currentX, $currentY);
			$this->writeText($this->getTitle(),$currentX,$currentY,$report);
		}
		
		foreach($children as $child) {
			
			$heightOfNext = $child->calcHeight($report);
			
			$isPageAdded = $report->checkPageBreak($heightOfNext, $currentX, $currentY);
			
			if ($this->getTitle() && $isPageAdded) $this->writeText($this->getTitle()." (continued)",$currentX,$currentY,$report);
			
			list($childWidth,$childHeight, $left, $top) = $child->createReportElement($report, $currentX, $currentY);
			$childHeight = $child->calcHeight($report);
			$currentY = $currentY + $childHeight;
			
			if ($childWidth > $childrenWidth) $childrenWidth = $childWidth;
			$childrenHeight+=$childHeight;
		}
		
		return array($childrenWidth,$currentY - $prevY,$currentX,$currentY);
	}

	function calcHeight($report) {	
		$children = $report->getChildFields($this->getName());
		$height = 0;
		foreach($children as $child) {
			$height += $child->calcHeight($report);
		}
		return $height;
	}

	function addChildFieldsToReport($pageName, $field,$form,$report,$level) {
		$childFields = $form->getChildFields($field);
		$level++;
        $ff = $reportFf = ADD::sys()->getService(ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY)->getFactory(ClinicLE\System\Factory\SystemFactories::REPORT_FIELD);
		foreach($childFields as $childField) {
			$rptFieldData = array();
			if (is_subclass_of($childField,"ADD_Field_Container")) {
				$rptFieldData = array(
					"Level" => $level,
					"Type" => $this->_typeMap[$childField->getType()],
					"Title" => $childField->getTitle(),
					"Field" => $pageName.".".$childField->getName(),
					"Settings" => $childField->getSettings(),
				);
				
			}
			else {
				$rptFieldData = array(
					"Level" => $level,
					"Type" => "Obs",
					"Field" => $pageName.".".$childField->getName()
				);
			}
			$rptField = $ff->newInstance($rptFieldData);
			$report->addField($rptField);
			
			if (is_subclass_of($childField,"ADD_Field_Container")) {
				$this->addChildFieldsToReport($pageName, $childField,$form,$report,$level);
			}
		}
		
	}
	
	function determineObsBlockForm() {
		$add = ADD::sys();
		$temp = explode(".",$this->getName());
		$pageName = $temp[0];
		$modName = $add->getParam('module');
		$mod = $add->getModule($modName);
		
		$page = $mod->getPage($pageName);
		if (!$page) {
			throw new ADD_ReportException("Page '$pageName' not found for obs block ".$this->getName());
		}
		$form = $page->getComponent('Form');
		$form->setup();
		$fieldName = count($temp)==1 ? null : $temp[1];
		
		return array($pageName,$fieldName,$form);
	}
}
?>
