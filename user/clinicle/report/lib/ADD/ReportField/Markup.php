<?php

class ADD_ReportField_Markup extends ADD_ReportField {
	protected $drupalElementType=null;
	protected $stored = false;
	
	public function createReportElement($page, $currentX, $currentY) {
		parent::createReportElement($page,$currentX,$currentY);
		$pdf = $page->getPdf();
		$font = $this->getSetting('font',"Arial");
		$fontDec = $this->getSetting('decoration','');
		
		$currentY = $this->getTop($page,$currentY);
		$currentX = $this->getLeft($page, $currentX);
		$align = $this->getSetting('align','L');
		$width = $this->getSetting('width');
		$border = $this->getSetting('border','0');
		
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		$value = $this->getTitle();
	
		$pdf->SetFont($font,$fontDec,$fontSize);
		
		$pdf->SetXY($currentX,$currentY);
		
		if (!$value) $value=" ";
		$valueWidth = $pdf->GetStringWidth($value);
		if ($width) {
			$pdf->Cell($width,$currentY,$value,$border,0,$align);
		}
		else {
			$pdf->Write($lineHeight * 1.2,$value);
		}
		if ($this->getSetting('width')) $valueWidth = $this->getSetting('width');
		$height = $this->calcHeight($page);
		$currentY+=$height;
		return array($valueWidth+2, $height, $currentX, $currentY);
	}
	
	function calcHeight($page) {
		list($fontSize, $lineHeight) = $this->getLineHeight($this->getSetting('size',10));
		$gap = $this->getSetting('gap',0);
		return ($lineHeight * 1.2) + $gap;
	}
}
?>