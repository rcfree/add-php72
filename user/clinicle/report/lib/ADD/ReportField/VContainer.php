<?php

class ADD_ReportField_VContainer extends ADD_ReportField {
	function createReportElement($report, $currentX, $currentY) {
		$pdf = $report->getPdf();
		$currentX = $this->getLeft($report,$currentX);
		$currentY = $this->getTop($report,$currentY);
		
		$children = $report->getChildFields($this->getName());
		$childrenHeight = ($pdf->CurPageSize[0] - 10) - $currentY;
		$childrenWidth = 0;
		$childFixedHeight = $childrenHeight/count($children);
		$prevX = $currentX;
		$prevY = $currentY;		
		
		foreach($children as $child) {
			
			$heightOfNext = $child->calcHeight($report);
			
			$nextY = $currentY + $heightOfNext;
			$allowHeight = $report->getReportConfig()->getReportSection()->getSetting('allow_height',250);
			if ($nextY > $allowHeight) {
				//list($currentX, $currentY) = $report->addPageBreak();
			}
			$pdf->SetY($currentY);
			
			list($childWidth, $childHeight) = $child->createReportElement($report, $currentX, $currentY);
			$currentY += $this->getSetting('auto-height') ? $childHeight : $childFixedHeight;
			
			if ($childWidth > $childrenWidth) $childrenWidth = $childWidth;
		}
		
		return array($childrenWidth,$childrenHeight,$prevX,$prevY);
	}
	function calcHeight($report) {
		$children = $report->getChildFields($this->getName());
		$height = 0;
		foreach($children as $child) {
			$height += $child->calcHeight($report);
		}
		return $height;
	}
	
}
?>