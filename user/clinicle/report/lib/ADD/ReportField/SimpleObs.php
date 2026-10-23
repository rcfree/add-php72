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

class ADD_ReportField_SimpleObs extends ADD_ReportField_Obs {
	public function createReportElement($page,$currentX,$currentY) {
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
		list($width,$height) = $this->createPdfElement($page,$currentX,$currentY);
	
		return array($width, $height, $currentX+$width, $currentY+$height);
	}
	const HEIGHT_FACTOR=0.8;
	function createPdfLabel($page,$currentX,$currentY) {
		
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		$pdf = $page->getPdf();
		
		$title = $this->createTitle($page);
		
		$pdf->SetFont('Arial','B',$fontSize);
		$labelWidth = $pdf->GetStringWidth($title);
		$pdf->SetXY($currentX,$currentY);
		
		$valueHeight = $this->getSetting('height',$lineHeight * self::HEIGHT_FACTOR);
		$pdf->SetTextColor(80,80,80);
		$pdf->Write($valueHeight,$title);
		return $labelWidth;
	}
	function createPdfElement($page,$currentX,$currentY) {
		$pdf = $page->getPdf();
		$gap = $this->getSetting('gap',2);
		$labelWidth = $this->createPdfLabel($page,$currentX,$currentY);
		
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		$valueHeight = $this->getSetting('height',$lineHeight * self::HEIGHT_FACTOR);
		
		$pdf->SetFont('Arial','',$fontSize);
		$border = $this->getSetting('border',1);
		
		if ( $page->isEmpty() ) {
			$value = "";
		}
		else {
			$value = $this->createValue($page);
		}
		
		$valueWidth = $this->getValueWidth($value,$page);
		
		$fill = $this->getSetting('no-fill') ? false : true;
		
		$pdf->SetDrawColor(255,255,255);
		$pdf->SetFillColor(255,255,255);
		
		$width = $labelWidth + $valueWidth;
		$pdf->SetY($currentY);
		
		$pdf->SetX($currentX+$labelWidth+($gap/2));
		
		$pdf->SetTextColor(0,0,0);
		$pdf->Write($valueHeight,$value);
		
		$height = $valueHeight;
		return array($width,$height + $gap);
	}
	function createValue($page) {
		if ($this->getSetting('value-only')) {
			$value = $this->_field->getValue();
			if (is_array($value)) $value=implode(",",array_keys($value));
		}
		else {
			$value = $this->_field->getDisplayText($page,null);
		}
		return $value;
	}
	function createTitle($page) {
		$title = $this->getTitle();
		if (!$title) $title = $this->_field->getTitle();
		if ($title=="&rarr;") {
			$title=chr(187);
		}
		$title=strip_tags($title);
		$suffix = $this->_field->getSetting('suffix');
		if($suffix) {
			$suffix = strip_tags($suffix);
			$title.=" ($suffix)";
		}
		$title.=":";
		return $title;
	}
}
