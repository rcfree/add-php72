<?php

class ADD_Content_Html_TableHelper {
	private $_headings = array();
	private $_rows = array();
	private $_tableClass;
	private $_extendedClass;
	private $_htmlAttrs = array();
	private $_description;
	private $_widths = array();
	
	function __construct($description, $htmlAttrs=array(), $extendedClass=null, $tableClass="table table-hover table-condensed table-striped") {
		$this->_description = $description;
		$this->_htmlAttrs = $htmlAttrs;	
		$this->_tableClass = $tableClass;
		$this->_extendedClass = $extendedClass;

	}
	
	
	function setHeadings($headings) {
		$this->_headings = $headings;
	}
	function setWidths($widths) {
		$this->_widths=$widths;
	}
	function addRow($row) {
		$this->_rows[]=$row;
	}
	
	function create() {
		$html = new ADD_Content_Html();
		
		$tableClass = $this->_tableClass;
		if ($this->_extendedClass) $tableClass.=" ".$this->_extendedClass;
		
		$attrs = $this->_htmlAttrs;
		$attrs['class']=$tableClass;
		
		$html->startElement("table",$attrs);
		if($this->_description) {
			$html->addTextElement("caption",$this->_description);
		}
		if($this->_headings) {
			$html->startElement("thead");
			$html->startElement("tr");
			
			$hcount=0;
			foreach($this->_headings as $heading) {
				list($thValue,$thAttrs)=$this->_extractValueAndAttrs($heading,$hcount);
				
				$html->addTextElement("th",$thValue,$thAttrs);
				$hcount++;
			}
			$html->endElement("tr");
			$html->endElement("thead");
		}
		$html->startElement("tbody");
		
		foreach($this->_rows as $row) {
			$rattrs=ADD_Util::val('attrs',$row,array());
			if (isset($row['class'])) $rattrs['class']=$row['class'];
			
			$html->startElement("tr",$rattrs);
			if (!isset($row['data'])) throw new ADD_MissingTableDataException("No 'data' element in row");
			$ccount=0;
			foreach($row['data'] as $td) {
				list($tdValue,$tdAttrs)=$this->_extractValueAndAttrs($td,$ccount);
				$html->addTextElement("td",$tdValue,$tdAttrs);
				$ccount++;
			}
			$html->endElement("tr");
		}
		$html->endElement("tbody");
		$html->endElement("table");
		return $html;
	}

	function _extractValueAndAttrs($cell, $colCount=null) {
			
		if(is_array($cell)) {
			$cellValue=ADD_Util::val(0,$cell);
			$cellAttrs=ADD_Util::val(1,$cell,array());	
		}
		else {
			$cellValue = $cell;
			$cellAttrs=array();
		}
		
		if($colCount===null) {
			return array($cellValue,$cellAttrs);
		}
		
		$headingWidth = ADD_Util::val($colCount,$this->_widths);
		
		if($headingWidth) {
			if (isset($cellAttrs['style'])) {
				$cellAttrs['style'].=';width:'.$headingWidth;
			}
			else {
				$cellAttrs['style']='width:'.$headingWidth;
			}
		}
			
		return array($cellValue,$cellAttrs);
	}
}
class ADD_MissingTableDataException extends Exception { }