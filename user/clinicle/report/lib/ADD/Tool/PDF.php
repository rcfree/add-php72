<?php

class ADD_Tool_PDF extends \FPDF {
	private $_report = null;
	function __construct($orient, $unit, $size,$report) {
		$this->_report=$report;
		parent::__construct($orient,$unit,$size);
	}
	function getCurPageSize() {
		return $this->CurPageSize;
	}
	function Header() {
		$report = $this->_report;
		$hField = $report->getField('header');
		if (!$hField) return;
		$children = $report->getChildFields($hField->getName());

		$currentX = 0;
		$currentY = 0;
		
		foreach($children as $child) {
			$child->createReportElement($this->_report, $currentX, $currentY);
		}
	}
	
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','',10);
		// Page number
		 
		$pageNo = $this->PageNo();
		 
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}