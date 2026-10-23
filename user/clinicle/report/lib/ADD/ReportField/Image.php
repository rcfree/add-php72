<?php

class ADD_ReportField_Image extends ADD_ReportField {
	protected $drupalElementType='item';
	protected $stored = false;
	function createReportElement($report,$currentX,$currentY) {
		$add = ADD::sys();
		$reportImagePath = $add->getPath()."/".$add->assertConfig('image_path');
        if (!is_dir($reportImagePath)) throw new ADD_ReportException("Report image path '$reportImagePath' does not exist");
		$pdf = $report->getPdf();
		$currentX = $this->getTop($report,$currentX);
		$currentY = $this->getLeft($report,$currentY);
		$pdf->SetXY($currentX,$currentY);
		$file = $reportImagePath."/".$this->getSetting('file');
		$pdf->Image($file);
		return array(0,0,0,0);
	}
}
?>