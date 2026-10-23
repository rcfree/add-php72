<?php
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * This is the derived DataFile class which loads Excel files.
 * Each row is put into an associative array (based on the file header) and added to an array accessible through getDataItems().
 * @author Rob Free
 * @version 0.1
 * @package LRB_DataFile_ExcelBase
 */

class LRB_DataFile_Excel extends LRB_DataFile_Base {

	/**
	 * Load Excel data file as array of associative arrays into dataItems property of DataFile object
	 * @param string $fileName
	 */
	function load() {
		$row = 1;
		$dataItems = array();
		$this->setDataItems(null);

		#used to allow \n line endings (PHP doesn't support this by default!)
		#ini_set("auto_detect_line_endings", true);
		$excel = IOFactory::load($this->getFileName());
		$sheet = $excel->getActiveSheet();
		$high = ord($sheet->getHighestDataColumn())-64;

		$headings = array();
		
		for($c=0;$c<$high;$c++) {
			$cell = $sheet->getCell(Coordinate::stringFromColumnIndex($c + 1).'1');
			$headings[]=$cell->getValue();
		}
		#if headings map data as assoc array using $headings
		for($r=2;$r<=$sheet->getHighestRow();$r++) {
			$c = 0;
			$item = array();
			$emptyCells = 0;
			foreach ($headings as $heading) {
				if (strlen($heading)==0) { $c++; continue; }
				if (isset($item[$heading])) throw new Exception("Column '$heading' appears multiple times in file (".$this->getFileName().")");	
				$value = $sheet->getCell(Coordinate::stringFromColumnIndex($c + 1).$r)->getValue();
				if (strlen($value)==0) $emptyCells++;
				$item[$heading]="$value";
				$c++;
			}
			if ($emptyCells<count($headings)) $dataItems[] = $item;
		}

		$this->setDataItems($dataItems);
	}

}
