<?php

	class ADD_Content_Pdf {
		private $_pdf = null;
		private $_currentX = null;
		private $_currentY = null;
		private $_pageBreak=false;
        private $_buffer = null;


		function init($page) {
			$pdf = new ADD_Tool_PDF('P','mm','A4', $page);
			$this->setPdf($pdf);
			$pdf->SetTopMargin(30);
			$pdf->SetAutoPageBreak(false);
			$pdf->AliasNbPages();
			$pdf->AddPage();
			$this->setCurrentX(10);
			$this->setCurrentY(10);
			$this->_pdf = $pdf;
		}
		
		function __toString() {
			return $this->output();
		}
        function output() {
            return $this->_populateBuffer();
		}
		function toFile($reportFileName) {
			$reportDir = dirname($reportFileName);
			
			if (!is_writable($reportDir)) throw new ADD_ReportException("You cannot write to directory $reportDir");
            
            $result = file_put_contents($reportFileName,$this->_populateBuffer());
            if ($result===FALSE) throw new ADD_ContentException("Unable to write file ".$reportFileName.'.pdf');
        }


        function _populateBuffer() {
            if (!$this->_pdf) return "";
            if (!$this->_buffer) {
                $this->_buffer = $this->_pdf->Output(null, 'S');
            }
            return $this->_buffer;
        }
        function clearBuffer() {
            $this->_buffer=null;
        }
		function addPageBreak() {
			$pdf = $this->getPdf();
			$this->getPdf()->addPage();
			$currentX = 10;
			$currentY = 38;
			$pdf->SetX($currentX);
			$pdf->SetY($currentY);
			$this->setCurrentX($currentX);
			$this->setCurrentY($currentY);
			return array($currentX, $currentY);
		}
		public function getPdf() {
			return $this->_pdf;
		}
		public function setPdf($pdf) {
			$this->_pdf = $pdf;	
		}
		public function getCurrentX() {
			return $this->_currentX;
		}
		public function getCurrentY() {
			return $this->_currentY;
		}
		public function setCurrentX($currentX) {
			$this->_currentX = $currentX;
		}
		public function setCurrentY($currentY) {
			$this->_currentY = $currentY;
		}	
		function checkPageBreak($ro, $heightOfNext, &$currentX,&$currentY) {
                    if ($this->_pageBreak) { $this->_pageBreak=false; return false; }
                    $this->_pageBreak=false;
                    $nextY = $currentY + $heightOfNext;
                    $allowHeight = $ro->getReportConfig()->getReportSection()->getSetting('allow_height',270) + 8;

                    if ($nextY <= $allowHeight) return false;

                    list($currentX, $currentY) = $this->addPageBreak();
                    return true;
            }
		function mimeType() {
			return "application/pdf";
		}
		function headers() {
			return null;
		}
	}
?>