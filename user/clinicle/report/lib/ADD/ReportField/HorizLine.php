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

class ADD_ReportField_HorizLine extends ADD_ReportField {
	public function createReportElement($page,$currentX,$currentY) {
		$pdf = $page->getPdf();
		$gap = $this->getSetting('gap',2);
		$currentX = $this->getLeft($page,$currentX);
		$currentY = $this->getTop($page,$currentY);
		$margin = $this->getSetting('margin',10);
		$width = 210 - $margin;
		$height = $gap*2;
		$pdf->SetDrawColor(160,160,160);
		$pdf->Line($margin,$currentY+$gap,$width,$currentY+$gap);
		
		return array($width, $height, $currentX+$width, $currentY+$height);
	}
}
