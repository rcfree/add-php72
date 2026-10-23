<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SimpleObs
 *
 * @author rcf8
 */

class ADD_ReportField_MultiObs extends ADD_ReportField_SimpleObs {
	const HEIGHT_FACTOR=1.0;
	function createPdfElement($page,$currentX,$currentY) {
		$pdf = $page->getPdf();
		$gap = $this->getSetting('gap',2);
		$labelWidth = $this->createPdfLabel($page,$currentX,$currentY);
		
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		$valueHeight = $this->getSetting('height',$lineHeight * self::HEIGHT_FACTOR);
		
		$pdf->SetFont('Arial','',$fontSize);
		
		$value = $this->createValue($page);

		//$valueWidth = $this->getValueWidth($value,$page);
		
		$fill = $this->getSetting('no-fill') ? false : true;
		
		$pdf->SetDrawColor(255,255,255);
		$pdf->SetFillColor(255,255,255);
		
		//$width = $labelWidth + $valueWidth;
		$width = 50;
		$currentY+=$valueHeight+1;
		$height = $valueHeight+1;
		
		$pdf->SetTextColor(0,0,0);
		foreach($value as $item) {
			$pdf->SetY($currentY);
			$pdf->SetX($currentX);
			$pdf->Write($valueHeight,trim($item));
			$currentY=$currentY+$valueHeight+0.5;
			$height+=$valueHeight+0.5;
		}
		return array($width,$height + $gap);
	}
	function createValue($page) {
		$value = explode(",",$this->_field->getDisplayText($page,null));
		if($value[0]=="- no options selected -") $value[0]="none";
		return $value;
	}
	function calcHeight($report) {
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		$value = $this->createValue($report);
		$valueHeight = $this->getSetting('height',$lineHeight * self::HEIGHT_FACTOR);
		return (($valueHeight + 0.5) * count($value)) + $valueHeight;
	}
}
