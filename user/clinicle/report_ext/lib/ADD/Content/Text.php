<?php
class ADD_Content_Text implements ADD_ContentInterface {
    protected $_textLines = array();
	protected $_fileName;
	protected $_currentX=0;
	protected $_currentY=0;
	protected $_height=0;
	
	function init() {
		$report = func_get_arg(0);
		$this->_width = $report->getReportConfig()->getReportSection()->getSetting('width',80);
	}
	function output() {
		if(!$this->_fileName) throw new ADD_ReportException("Text file not written");
		if(!file_exists($this->_fileName)) throw new ADD_ReportException("File '{$this->_fileName}' does not exist");
		$content = file_get_contents($this->_fileName);
		return $content;
	}
        
	function __toString() {
		try {
			return $this->output();
		}
		catch(Exception $ex) {
			return "<div class='alert alert-error'>Unexpected error:".ADD::web()->errorMessage($ex)."</div>";
		}
	}
        
        function addText($text,$x=null,$y=null) {
        	if($x===null) $x=$this->_currentX;
			if($y===null) $y=$this->_currentY;
			foreach(str_split($text) as $char) {
        		$this->_textChars[$y][$x]=$char;
				$x++;
			}
			if($y>$this->_height) $this->_height=$y;
        }
        
        function mimeType() {
            return "text/plain";
        }
		function headers() {
			return null;
		}
		function clearBuffer() {}
		function toFile($reportFileName) {
		$reportDir = dirname($reportFileName);
			
		if (!is_writable($reportDir)) throw new ADD_ReportException("You cannot write to directory $reportDir");
        $rows = array();
        for($curY=0;$curY<=$this->_height;$curY++) {
        	$row = "";
        	for($curX=0;$curX<$this->_width;$curX++) {
        		if(!isset($this->_textChars[$curY])) continue;
        		$char = ADD_Util::val($curX,$this->_textChars[$curY]," ");
        		$row.=$char;
			}
			$rows[]=$row;
		}    
        file_put_contents($reportFileName,implode("\n",$rows));
		$this->_fileName = $reportFileName;
	}

	function getCurrentX() {
		return $this->_currentX;
	}
	function getCurrentY() {
		return $this->_currentY;
	}
	function setCurrentX($currentX) {
		$this->_currentX=$currentX;
	}
	function setCurrentY($currentY) {
		$this->_currentY=$currentY;
	}
	
	function addToCurrentX($addToX) {
		$this->setCurrentX($this->_currentX+intval($addToX));
	}
	function addToCurrentY($addToY) {
		$this->setCurrentY($this->_currentY+intval($addToY));
	}
	
	function setWidth() {
		$this->_width=$width;
	}
}