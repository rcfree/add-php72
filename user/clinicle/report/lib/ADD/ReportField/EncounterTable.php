<?php

class ADD_ReportField_EncounterTable extends ADD_ReportField {
	
	function generateTable($report) {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		$rowEncs = $report->isEmpty() ? array() : $this->getRowEncounters($report);
		$headers = array();
		$results = array();
	
		$childFields = $report->getChildFields($this->getName());
		foreach($childFields as $childField) {
			$headers[]=array(
				'data'=>$childField->getTitle(),
				'width'=>$childField->getSetting('width'),
				'decoration' => $this->getSetting('header_decoration','B'),
				'font' => $this->getSetting('header_font','Arial'),
				'size' => $childField->getSetting('header_size',$this->getSetting('header_size',10)),
				'align' => $childField->getSetting('align','L')
			);
		}
		$filterFields = ADD_Util::makeArray($this->getSetting('filter_field'));
		$filterValues = ADD_Util::makeArray($this->getSetting('filter_value'));
				
		foreach($rowEncs as $encId => $enc) {
			$result = array();
			$values = $db->getObservationsInEncounter($enc);
			$values['!date_created']=$enc['date_created'];
			$values['!date_updated']=$enc['date_updated'];
			$fcount=0;
			$filtered = false;
			foreach($filterFields as $filterField) {
				if (isset($values[$filterField])) {
					if ($values[$filterField]!=$filterValues[$fcount]) $filtered=true;
				}
				$fcount++;
			}
			if ($filtered) continue;
			foreach($childFields as $childField) {
				$childField->populateValue($values,$report,true);
				$cell = $childField->getDisplayText($report,null);
				$result[]=array(
					'data'=>$cell,
					'width'=>$childField->getSetting('width'),
					'decoration' => $childField->getSetting('decoration',''),
					'font' => $childField->getSetting('font','Arial'),
					'align' => $childField->getSetting('align','L'),
					'size' => $childField->getSetting('size',10),
				);
			}  
			$results[]=$result;
		}
		$minRows = $this->getSetting('min-rows');
		if ($minRows && count($results)<$minRows) {
			for($i=count($results);$i<$minRows;$i++) {
				$result = array();
				foreach($childFields as $childField) {
					$result[]=array(
						'width'=>$childField->getSetting('width'),
						'decoration' => $childField->getSetting('decoration',''),
						'font' => $childField->getSetting('font','Arial'),
						'align' => $childField->getSetting('align','L'),
						'size' => $childField->getSetting('size',10),
					);
				} 
				$results[]=$result;
			}
		}
		return array($headers,$results);
	}
	
	function createReportElement($report, $currentX, $currentY) {
		$add = ADD::sys();
		
		$pdf = $report->getPdf();
		list($headers,$results) = $this->generateTable($report);
		
		$currentY = $this->getTop($report,$currentY);
		$currentX = $this->getLeft($report, $currentX);
		
		$prevX = $currentX;
		$prevY = $currentY;
		$overallHeight = 0;
		$overallWidth = 0;
		$margin = 2;
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		$headingHeight = $this->getSetting('heading_height',($lineHeight+$margin));
		$this->drawHeaders($headers,$report,$currentX, $currentY,$margin);
		
		$currentY+=$headingHeight;
		$overallHeight+=$headingHeight;
		
		foreach($results as $result) {
			$currentX = $prevX;
			foreach($result as $cell) {
				$font = $cell['font'];
				$fontDec = $cell['decoration'];
			
				$align = $cell['align'];
				$width = $cell['width'];
				$pdf->SetFont($font,$fontDec,$fontSize);
			
				$value = isset($cell['data']) ? $this->truncateText($cell['data'],$width,$report) : "";
				
				
				$pdf->SetXY($currentX,$currentY);
				
				$pdf->Cell($width+$margin,$lineHeight+$margin,$value,1,0,$align);
				
				$overallWidth+=$width+$margin;
				$currentX+=$width+$margin;
			}
			$overallHeight+=$lineHeight+$margin;
			$currentY+=$lineHeight+$margin;
			if ($currentY > 260) {
				list($currentX,$currentY) = $report->getContent()->addPageBreak();
				$this->drawHeaders($headers,$report,$currentX, $currentY,$margin);
				$currentY+=$headingHeight;
				$overallHeight+=$headingHeight;
			}
		}
		return array(0,$overallHeight,$currentX,$currentY);
	}
	
	function drawHeaders($headers,$report, $currentX, $currentY,$margin) {
		$pdf = $report->getPdf();
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		$headingHeight = $this->getSetting('heading_height',($lineHeight+$margin));
		foreach($headers as $header) {
			$font = $header['font'];
			$fontDec = $header['decoration'];
			$align = $header['align'];
			$width = $header['width'];
			$value = $header['data'];
			$size = $header['size'];
			
			$pdf->SetFont($font,$fontDec,$size);
		
			$pdf->SetXY($currentX,$currentY);
			$pdf->SetFillColor(220,220,220);
			$lines = preg_split('/\r|\n/',$value);
			if (count($lines)==2 && $lines[1]==" ") array_pop($lines);
			
			if (count($lines)==1) {
				$pdf->Cell($width+$margin,$headingHeight,$value,1,0,$align,true);
			}
			else {
				$lineY = $currentY;
				$pdf->Rect($currentX,$currentY,$width+$margin,$headingHeight,"DF");
				foreach($lines as $line) {
					$lineY+=$lineHeight;
					$pdf->Text($currentX+1,$lineY,$line,1,1);
				}
			}
			
			$currentX+=$width+$margin;
		}
	}
	function getRowEncounters($page) {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		$patientId =$add->getPatientId();
		
		$pencId = $add->getParam('id');
		if(!$pencId) throw new Exception("No 'id' param provided for encounter table");
		$penc = $db->getSection($pencId);
		
		$subEncType = $this->getSetting('encounter');
		$encBits = explode(".",$subEncType);
		if ($this->getSetting('from_parent')) $penc = $penc->getParentEncounter();
		
		$cencs = $db->getChildEncountersWithType($penc, $subEncType);
		
		$rowEncounters = array();
		foreach($cencs as $cenc) {
			$rowEncounters[$cenc['id']] = $cenc;
		}
		return $rowEncounters;
	}
	
	function calcHeight($page) {
		$add = ADD::sys();
		
		$pdf = $report->getPdf();
		list($headers,$results) = $this->generateTable($report);
		
		$overallHeight = 0;
		$overallWidth = 0;
		$margin = 2;
		list($fontSize, $lineHeight) = $this->getLineHeight(10);
		$headingHeight = $this->getSetting('heading_height',($lineHeight+$margin));
		$this->drawHeaders($headers,$report,$currentX, $currentY,$margin);
		
		$overallHeight+=$headingHeight;
		
		foreach($results as $result) {
			$overallHeight+=$lineHeight+$margin;
		}
		return $overallHeight;
	}
}
?>
