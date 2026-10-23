<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Report_PlainText extends ADD_Report_Standard {

	
function getContentClass() {
	return "Text";
}	

function generate() {
		$add = ADD::sys();
		$topLevelFields = $this->_fieldContent->getTopLevelFields();
		$cont = $this->_content;
		
		$currentY = $cont->getCurrentY();
		$currentX = $cont->getCurrentX();
		$fieldCount = 0;
		
		foreach($topLevelFields as $fieldName) {
			$field = $this->getField($fieldName);
			
			$cont->setCurrentX(0);
			
			list($width,$height) = $this->_outputField($field);
			
			$cont->addToCurrentY($height);
			
			$fieldCount++;
		}
	}
	function _outputField($field) {
		$content = $this->_content;
		$rsection = $this->getReportConfig()->getReportSection();
		if($field->getSetting('top')!==null) {
			$content->setCurrentY($field->getSetting('top'));
		}
		if($field->getSetting('left')!==null) {
			$content->setCurrentX($field->getSetting('left'));
		}
			 
		$currX = $content->getCurrentX();
		$currY = $content->getCurrentY();
		
		
		if(is_a($field,"ADD_ReportField_SimpleObs")) {
			
			$title = $field->createTitle($this);
			$height=0;
			$length = 0;
			if($rsection->getSetting('label_width')) {
				$title=sprintf("%".$field->getSetting('label_width',$rsection->getSetting('label_width'))."s",$title);
			}
			
			$options = $field->getSetting('stack') ? explode(",",$field->createValue($this)) : array($field->createValue($this));
			$content->addText($title,$currX,$currY);
			
			foreach($options as $option) {
				$option = trim($option);
				$content->addText($option,$currX+strlen($title)+1,$currY+$height);
				$height++;
				$optLength = strlen($title.$option)+1;
				if($optLength>$length) $length = $optLength;
			}
			
			return array($length,$height);
		}
		if(is_a($field,"ADD_ReportField_EncounterTable")) {
			
			list($headers,$rows) = $field->generateTable($this);
			
			$origX = $currX;
			$bar = "";
			$height = 0;
			$colCount = 0;
			
			$noBorder = $field->getSetting('no_border');
			$noHeadings = $field->getSetting('no_headings');
			$autoSize = $field->getSetting('auto_size');
					
			if(!$noHeadings) {
				foreach($headers as $header) {
					$lastCol = count($headers)-1==$colCount;
					$headerSize = $header['size'];
					$headerData = $header['data'];
					$headerData = sprintf("%-{$headerSize}s",$headerData);
					$content->addText($headerData.($lastCol || $noBorder ? " " : "|"),$currX,$currY);
					$bar .= str_repeat("-",$headerSize).($lastCol || $noBorder ? " " : "+");
					$currX = $currX + $headerSize + 1;
					$colCount++;
				}
			}
			
			$height++;
			
			$content->addText($bar,$origX,$currY+$height);
			$height++;
			
			foreach($rows as $row) {
				$colCount = 0;
				$currX=$origX;
				foreach($row as $cell) {
					$cellData = ADD_Util::val('data',$cell);
					$colSize = $autoSize ? strlen($cellData) : $headers[$colCount]['size'];
					
					
					if(!$autoSize) {
						if(strlen($cellData)>$colSize) {
							$cellData = substr($cellData,0,$colSize);
						}
						else {
							$cellData = sprintf("%-{$colSize}s",$cellData);
						}
					}
					
					$content->addText($cellData.($noBorder ? " " : "|"),$currX,$currY+$height);
					$currX = $currX + $colSize + 1;
					$colCount++;
				}

				$height++;
			}

			return array(0,$height);
		}
		if(is_a($field,"ADD_ReportField_HContainer")) {
			return $this->_outputChildFields($field);
		}
		if(is_a($field,"ADD_ReportField_HorizLine")) {
			$content->addText(str_repeat("-",$rsection->getSetting('width')),null,$content->getCurrentY());
			return array(0,1);
		}
		if(is_a($field,"ADD_ReportField_TextHeader")) {
			
			$height=2;
				
			$sectionHeader = $field->getTitle();
			$currentX=0;
			if($field->getSetting('align')=='C') {
				$currX = intval(($rsection->getSetting('width')-strlen($sectionHeader))/2);
			}
			$content->addText($field->getTitle(),$currX,$currY);
			
			$type = $field->getSetting('type');
			if($type==1) {
				$content->addText(str_repeat("=",strlen($field->getTitle())),$currX,$currY+1);
			}
			if($type==2) {
				$content->addText(str_repeat("-",strlen($field->getTitle())),$currX,$currY+1);
			}
			if($type==3) {
				$content->addText(str_repeat(" ",strlen($field->getTitle())),$currX,$currY+1);
			}
			if($field->getSetting('extra_cr')) {
				$height++;
			}
			return array(0,$height);
		}
		if(is_a($field,"ADD_ReportField_Markup")) {
			$fieldTitle = $field->getTitle();
			if($field->getSetting('align')=='C') {
				$currentX = intval(($rsection->getSetting('width')-strlen($fieldTitle))/2);
			}
			$content->addText($field->getTitle(),$currX,null);
			return array(0,1);
		}

	}
	function _outputChildFields($field) {
		$content = $this->_content;
		$childFields = $this->_fieldContent->getChildFields($field->getName());
		
		$childWidth = 0;
		$childHeight = 1;
		$rsection = $this->getReportConfig()->getReportSection();
		
		foreach($childFields as $childField) {
			list($width,$height) = $this->_outputField($childField);
			
			if($field->getSetting('segments')) {
				$width = $rsection->getSetting('width')/$field->getSetting('segments');
			}
			else {
				$width++;
			}
			$content->addToCurrentX($width);
			if($height>$childHeight) $childHeight=$height;
		}
		return array(0,$childHeight);
	}
	function getReportExtension() {
		return "txt";
	}
	function addGeneratedFieldName() { }
}