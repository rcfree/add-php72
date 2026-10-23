<?php

class ADD_ReportField_RBox extends ADD_ReportField {
	
	function createReportElement($report, $currentX, $currentY) {
		
		parent::createReportElement($report,$currentX, $currentY);
		$pdf = $report->getPdf();
		$children = $report->getChildFields($this->getName());
		$containerWidth = ($pdf->CurPageSize[0] - 10) - $currentX;

		$childWidth = count($children)==0 ? $containerWidth : $containerWidth/count($children);
		$gap = $this->getSetting('gap',2);
		
		$currentX = $this->getLeft($report,$currentX);
		$currentY = $this->getTop($report,$currentY) + $gap;
		
		$font = $this->getSetting('font',"Arial");
		$fontDec = $this->getSetting('decoration','B');
		
		$align = $this->getSetting('align','L');
		$width = $this->getSetting('width');
		$border = $this->getSetting('border','0');
		
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		$title = $this->getSetting('display_title',$this->getTitle());
			
		if (strlen($title)>0) {
			$pdf->SetFont($font,$fontDec,$fontSize);
			
			$pdf->SetXY($currentX,$currentY);
			$currentY += $lineHeight + $gap;
			
			if (!$title) $title=" ";
			$valueWidth = $pdf->GetStringWidth($title);
			if ($width) {
				$pdf->Cell($width,$currentY,$title,$border,0,$align);
			}
			else {
				$pdf->Write($lineHeight * 1.2,$title);
			}
		}
		
		$lmargin = $this->getSetting('lmargin',1);
		$rmargin = $this->getSetting('rmargin',1);
		$tmargin = $this->getSetting('tmargin',1);
		$bmargin = $this->getSetting('bmargin',1);
		
		$prevX = $currentX;
		$prevY = $currentY;
		
		if ($lmargin) $currentX+=$lmargin;
		if ($tmargin) $currentY+=$tmargin;
		
		$height = $this->getSetting('height',null);
		$width = $this->getSetting('width',null);
		
		$childrenHeight = 0;
		$childrenWidth = 0;
		$childCount = 0;
		foreach($children as $child) {
			
			list($childWidth,$childHeight, $childTop, $childLeft) = $child->createReportElement($report, $currentX, $currentY);
			$currentY+=$childHeight;
			if ($childWidth>$childrenWidth) $childrenWidth=$childWidth;
			$childrenHeight+=$childHeight;
			
			$childCount++;
			/*
			$nextChild = isset($children[$childCount+1]) ? $children[$childCount+1] : null;
			if ($nextChild) {
				$heightOfNext = $children[$childCount]->calcHeight($report);
				$report->checkPageBreak($heightOfNext, $currentX,$currentY);
			}
			 */
		}
		
		if ($height) {
			$childrenHeight = $height;
		}
		if ($width) {
			$childrenWidth = $width;
		}
		$boxWidth=$childrenWidth + $lmargin + $rmargin;
		$boxHeight = $childrenHeight + $tmargin + $bmargin;
		if($this->getSetting('border_colour')) {
			list($r,$g,$b) = explode(";",$this->getSetting('border_colour'));
			$pdf->SetDrawColor($r,$g,$b);
		}
		$pdf->Rect($prevX,$prevY,$boxWidth,$boxHeight);
		if (strlen($title)>0) $boxHeight += $lineHeight*2;
		$boxHeight+=$gap;
		
		return array($boxWidth,$boxHeight, $currentX, $prevY+$boxHeight);
	}

	function calcHeight($report) {
		$children = $report->getChildFields($this->getName());
		$tmargin = $this->getSetting('tmargin',1);
		$bmargin = $this->getSetting('bmargin',1);
		
		$height = $this->getSetting('height',null);
		$width = $this->getSetting('width',null);
		$gap = $this->getSetting('gap',2);
		
		
		$childrenHeight = 0;
		$childrenWidth = 0;
		foreach($children as $child) {
			$childHeight = $child->calcHeight($report);
			$childrenHeight+=$childHeight;
		}
		
		if ($height) {
			$childrenHeight = $height;
		}
		
		$boxHeight = $childrenHeight + $tmargin + $bmargin;
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		
		if (strlen($this->getTitle())>0) $boxHeight += $lineHeight*2 + $gap;
		return $boxHeight;
	}
}
?>