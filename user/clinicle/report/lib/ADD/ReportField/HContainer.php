<?php

class ADD_ReportField_HContainer extends ADD_ReportField {
	function createReportElement($report, $currentX, $currentY) {
		$pdf = $report->getPdf();
		$currentX = $this->getLeft($report,$currentX);
		$currentY = $this->getTop($report,$currentY);
		
		$children = $report->getChildFields($this->getName());
		if (count($children)==0) return;
		$childrenWidth = ($pdf->CurPageSize[0] - 15) - $currentX;
		$childrenHeight = 0;
		$gap = $this->getSetting('gap',2);
		
		$segments = $this->getSetting('segments');
		
		if ($segments) {
			if ($this->getSetting('auto-width')) throw new ADD_ReportException("Cannot have 'segments' setting with 'auto-width' setting");
			if ($segments > count($children)) {
				for($i=count($children);$i<$segments;$i++) {
					$children[]=null;
				}	
			}
		}
		
		$childFixedWidth = $childrenWidth/count($children);
		
		foreach($children as $child) {
			$pdf->SetX($currentX);
			if (!$child) {
				$currentX += $childFixedWidth;
				continue;
			}
			list($childWidth, $childHeight,$bottom,$right) = $child->createReportElement($report, $currentX, $currentY);
			$currentX += $this->getSetting('auto-width') ? $childWidth + ($gap*4) : $childFixedWidth;
			if ($childHeight > $childrenHeight) $childrenHeight = $childHeight;
		}
		
		return array($childrenWidth,$childrenHeight,$currentX, $currentY + $childrenHeight);
	}

	function calcHeight($report) {
		$children = $report->getChildFields($this->getName());
		$childrenHeight = 0;	
		
		foreach($children as $child) {
			if (!$child) continue;
			$childHeight = $child->calcHeight($report);
			if ($childHeight > $childrenHeight) $childrenHeight = $childHeight;
		}
		return $childrenHeight;
	}
}
?>