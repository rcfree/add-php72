<?php

class ADD_Field_Memo extends ADD_Field_Base {
	
	function addMarkup($page) {
		$add = ADD::sys();
		
		$html = $page->html();
		$html->addTextElement("label",$this->getTitle(),array("class"=>"control-label"),$this->getName()."_label");
		$opts = array(
			"name"=>$this->getName().($this->getIdValue() ? "[{$this->getIdValue()}]" : ""),
			"style"=>"width:".(($this->getSetting('size',30)/2)+1)."em;height:".$this->getSetting('height',3)."em",
			"id"=>$this->getIdValue() ? $this->getName()."-{$this->getIdValue()}" :"edit-".str_replace("_","-",$this->getName())
		);
		if ($add->hasState('readOnly') || $this->getSetting('viewonly') || $this->isReadOnly()) $opts['readonly']='readonly'; 
		
		$html->startElement("textarea",$opts);
		$html->addHtml($this->getValue());
		$html->endElement("textarea");
	}
	function getDisplayText($page,$id) {
		$value = $this->getValue();
		if(!is_a($page,"ADD_Component_ReportOutput")) $value = str_replace("\n","<br/>",$value);
		return $value;
	}
	
	function createPdfElement($rptField,$page,$currentX,$currentY) {
		$labelWidth = $this->createPdfLabel($rptField,$page,$currentX,$currentY);
		$pdf = $page->getPdf();
		$gap = $rptField->getSetting('gap',2);
		
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		$labelAbove = $rptField->getSetting('label-above',null);
		
		$valueHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		$memoHeight = $valueHeight * $this->getSetting('rows',3);
		
		$pdf->SetFont('Arial','',$fontSize);
		$value = $page->isEmpty() ? "" : $this->getDisplayText($page,null);
		$size = $rptField->getSetting('columns',80);
		$this->setSetting('size',$size);
		$valueWidth = $rptField->getValueWidth($value,$page);
		
		$fill = $rptField->getSetting('no-fill') ? false : true;
		
		$pdf->SetDrawColor(120,120,120);
		$pdf->SetFillColor(246,246,246);
		$border = $rptField->getSetting('border',1);
		
		$width = $rptField->calcWidth($labelWidth,$valueWidth,$labelAbove);
		if ($labelAbove) {
			$pdf->SetX($labelWidth+$currentX+$gap);
			
			$pdf->MultiCell($valueWidth+5,$memoHeight,$value,$border,1,"L","DF");
			return array($width + $gap, $memoHeight);
		}
		else {
			$pdf->Rect($currentX+$gap, $currentY+($lineHeight * 1.8)+1,$valueWidth,$memoHeight,"DF");
			$pdf->SetY($currentY+($lineHeight * 1.8)+$gap);
			$pdf->SetX($currentX+$gap);
			$pdf->MultiCell($valueWidth,$lineHeight,$value,null,"L","");
			$suffix = $this->getSetting('suffix');
			if ($suffix) {
				$suffix = strip_tags($suffix);
				$pdf->Write($memoHeight,$suffix);
				$width+=$pdf->GetStringWidth($suffix);
			}
			$height = $memoHeight + ($lineHeight * 1.8);
		
			return array($width,$height + $gap);
		}
	}
	function calcPdfHeight($rptField, $page) {
		$gap = $rptField->getSetting('gap',2);
		
		list($fontSize, $lineHeight) = $rptField->getLineHeight(10);
		$valueHeight = $rptField->getSetting('height',$lineHeight * 1.55);
		
		$memoHeight = $lineHeight * $this->getSetting('rows',3);
		$height = $memoHeight + ($lineHeight * 3);
		return $height+$gap;
	}

}
?>