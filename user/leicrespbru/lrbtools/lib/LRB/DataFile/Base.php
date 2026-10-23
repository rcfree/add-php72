<?php
/**
 * This is the base class for DataFile. Derivatives of this (e.g. DataFile_Excel) will load delimited-type file formats (e.g. CSV, Excel).
 * Each row is put into an associative array (based on the file header) and added to an array accessible through getDataItems().
 * @author Rob Free
 * @version 0.1
 * @package LRB_DataFile_Base
*/

class LRB_DataFile_Base {
	private $dataItems = null;
	private $fileName = null;
	private $headings = null;

/**
 * Constructor sets up {@link $dataItems} if given $fileName
 * @param string $fileName
 */
function __construct($fileName=null, $headings=null) {
	$this->setFileName($fileName);
	$this->setHeadings($headings);
}

function setDataItems($dataItems) {
	$this->dataItems = $dataItems;
}

function getDataItems() {
	return $this->dataItems;
}

function setHeadings($headings) {
	$this->headings = $headings;
}

function getHeadings() {
	return $this->headings;
}
function getFileName() { 
	return $this->fileName;
}
function setFileName($fileName) {
	$this->fileName = $fileName;
}
private $headerRow = false;
        
        function hasHeaderRow() {
            return $this->headerRow;
        }
        function setHeaderRow($headerRow) {
            $this->headerRow = $headerRow;
        }
/**
* Load data file as array of associative arrays into dataItems property of DataFile object
* @param string $fileName
*/
function load() { 
	throw new Exception("No 'load' method implemented"); 
}
}
