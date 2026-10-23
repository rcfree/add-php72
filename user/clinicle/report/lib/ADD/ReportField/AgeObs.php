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

class ADD_ReportField_AgeObs extends ADD_ReportField_SimpleObs {
	function createValue($page) {
		$formData = $page->getData();
		list($toPage,$toField) = explode(".",$this->getSetting('to_field'));
		$obs = $page->getObsCache($toPage);

		$toDate = $obs->getObsValue($toField);
		$fromDate = ADD_Util::val('patient.date_of_birth',$formData);
		if(!$toDate) return "No to date";
		return ADD_Field_Util::age($fromDate,$toDate);
	}
	function finaliseField($page) {
	}
	function createTitle($page) {
		return "Age:";
	}
	function beforePopulateFields($report) {}
	function afterPopulateFields($report) {}
	function calcHeight($page) {
		return 10;
	}
	public function createReportElement($page,$currentX,$currentY) {
		$pdf = $page->getPdf();
		$gap = $this->getSetting('gap',2);
		$currentX = $this->getLeft($page,$currentX);
		$currentY = $this->getTop($page,$currentY);
		
		list($width,$height) = $this->createPdfElement($page,$currentX,$currentY);
	
		return array($width, $height, $currentX+$width, $currentY+$height);
	}
	function getValueWidth($value,$page) {
		return $page->getPdf()->GetStringWidth($value)+0.5;
	}
}
