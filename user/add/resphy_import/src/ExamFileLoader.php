<?php
namespace ADD\ResphyImport;
use CLE;
use ClinicLE\Common\Util;
use ADD\ResphyImport\Exception\InvalidFileException;
use ADD\ResphyImport\Exception\FileIOException;

class ExamFileLoader {
	const FIELD_SEP="!";
	const FNAME_FIELD="F NAME";
	const LNAME_FIELD="L NAME";
	const IDENT_FIELD="IDENT.";
	const DOB_FIELD="D.O.B.";
	const PRED_SRC_FIELD="NORMAL";
	const HEIGHT_FIELD="HEIGHT";
	const WEIGHT_FIELD="WEIGHT";
	const TEST_DATE_FIELD="TEST DATE";
	const TEST_TIME_FIELD="TEST TIME";
	const CONSULTANT_FIELD="PHYSICIAN";
	
	private $_tfHandle;
	private $_formattedFields;
	private $examFilePath;

	function __construct($examFilePath) {
		$this->examFilePath = $examFilePath;
	}
	function getNextLine() {
		$nextLine = trim(fgets($this->_tfHandle));
		$chars = count_chars($nextLine, 1);
		
		if(isset($chars[33]) && $chars[33]>1) {
			throw new InvalidFileException("Format of test file is invalid");
		}
		return $nextLine;
	}
	function skipLines($noLines) {
		for($i=0;$i<$noLines;$i++) {
			fgets($this->_tfHandle);
		}
	}
	function getNextNonEmptyLine() {
		$line = fgets($this->_tfHandle);
				
		while(strlen($line)==0 || feof($this->_tfHandle)) {
			$line = fgets($this->_tfHandle);
		}
		return $line;
	}
	function setFormattedFields($formattedFields) {
		$this->_formattedFields = $formattedFields;
	}
	function hasFormattedFields() {
		return $this->_formattedFields;
	}
	function _populateExamDetails(ExamFile $examFile) {
		$detailData = array();
		$log = CLE::log($this);
		$line = $this->getNextNonEmptyLine();
		$matches = array();	
		while(preg_match("/^(.+)\s[\:](.*)$/",$line,$matches)) {
			$line = $this->getNextLine();
			if(strlen(trim($line))==0) {
				break;
			}
			$detailData[trim($matches[1])]=trim($matches[2]);
		}	
		$examFile->setLastName(Util::val(self::LNAME_FIELD,$detailData));
		$examFile->setFirstName(Util::val(self::FNAME_FIELD,$detailData));
		$examFile->setIdentifier(Util::val(self::IDENT_FIELD,$detailData));
		$examFile->setDateOfBirth(Util::val(self::DOB_FIELD,$detailData));
		$examFile->setPredictedSource(Util::val(self::PRED_SRC_FIELD,$detailData));
		
		$height = trim(str_replace("cm","",Util::val(self::HEIGHT_FIELD,$detailData)));
		if(!is_numeric($height)) {
			$log->warn("Height '".$height."' is not numeric in exam file:".$examFile->getFileName());
		}
		$examFile->setHeight($height);
		$weight = trim(str_replace("kg","",Util::val(self::WEIGHT_FIELD,$detailData)));
		if(!is_numeric($height)) {
			$log->warn("Weight '".$weight."' is not numeric in exam file:".$examFile->getFileName());
		}
		$examFile->setWeight($weight);
		$date = Util::val(self::TEST_DATE_FIELD,$detailData);
		$time = substr(Util::val(self::TEST_TIME_FIELD,$detailData),0,5);
		if(count($detailData)==0) {
			throw new InvalidFileException("File '".$examFile->getFileName()."' does not contain exam details");
		}
		if(!$date) {
			throw new InvalidFileException("File '".$examFile->getFileName()."' does not contain timestamp");
		}
		
		$examFile->setDateTimeOfTests($date." ".$time);
		$examFile->setConsultant(Util::val(self::CONSULTANT_FIELD,$detailData));
	}
	
	function _populateTestData(ExamFile $examFile) {
		$row = fgetcsv($this->_tfHandle,null,self::FIELD_SEP);
		$headings = array();
		while(count($row)==1) {
			$row = fgetcsv($this->_tfHandle,null,self::FIELD_SEP);
		}
		
		//into headings
		while(count($row)>1) {
			$ccount=0;
			foreach($row as $cell) {
				if(!isset($headings[$ccount])) $headings[$ccount]="";
				$headings[$ccount].=trim($cell)." ";
				$ccount++;
			}
			$row = fgetcsv($this->_tfHandle,null,self::FIELD_SEP);
		}
		$headings[0]="TEST";
		$headings[1]="UNITS";
		
		$tests=array();
		while(!feof($this->_tfHandle)) {
			$row = fgetcsv($this->_tfHandle,null,self::FIELD_SEP);
			$test = array();
			if(!is_array($row)) break;
			
			$ccount=0;
				
			foreach($row as $cell) {
				if(strlen(trim($cell))==0) {
					$ccount++;
					continue;
				}
				$test[trim($headings[$ccount])]=trim($cell);
				$ccount++;
			}
			$testName = trim($row[0]);
			if(!$testName) break;
			$tests[$testName]=$test;
		}
		$examFile->setTests($tests);
		
		$comments = array();
		while(!feof($this->_tfHandle)) {
			$row = fgets($this->_tfHandle);
			if(strlen(trim($row))==0) continue;
			$comments[]=trim($row);
		}
		$examFile->setComments(implode("\n",$comments));
		
	}
	
	function _populateDateTime(ExamFile $examFile) {
		$pathInfo = pathinfo($examFile->getFileName());
		$fileName = $pathInfo['filename'];
		$matches=array();
		preg_match("/_(\d{8}_\d{4})$/",$fileName,$matches);
		if(!isset($matches[1])) throw new InvalidFileException("File '$fileName' does not contain timestamp");
		$rawDateTime = $matches[1];
		$dt = DateTime::createFromFormat("Ymd_Hi",$rawDateTime);
		$examFile->setDateTimeOfTests($dt->format("d/m/Y H:i"));
	}
	
	function load(ExamFile $examFile) {
		$this->_tfHandle = fopen($examFile->getFile()->getRealPath(),"r");
		
		if (!flock($this->_tfHandle, LOCK_EX|LOCK_NB, $wouldblock)) {
    		if ($wouldblock) {
		    	throw new FileIOException(sprintf('Exam file %s is locked by another process', $examFile->getFile()
                    ->getRealPath()));
			}
			else {
				throw new FileIOException(sprintf('Exam file %s could not be locked for an unknown reason',
                    $examFile->getFile()
                        ->getRealPath()));
			}
		}
		
		
		$this->_populateExamDetails($examFile);

		$this->_populateTestData($examFile);
		fclose($this->_tfHandle);
		
		$this->_populatePdfFileNames($examFile);
		
	}
		
	function _populatePdfFileNames(ExamFile $examFile) {
		$pdfFileName = str_replace(".TXT",".PDF",$examFile->getFile()->getPathname());
		
		if(!file_exists($pdfFileName)) {
			return;
		}
		$examFile->setSourcePdfFileName($pdfFileName);
		$examFile->setTargetPdfFileName($examFile->generateUniqueId().".PDF");
	}
	function getFilePath() {
		return $this->examFilePath;
	}
}
