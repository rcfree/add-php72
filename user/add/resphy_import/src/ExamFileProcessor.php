<?php

namespace ADD\ResphyImport;
use ADD\ResphyImport\Exception\SystemCommandException;
use ClinicLE\Common\Exception\SystemUserException;
use ClinicLE\System\Service\CoreServices;
use ADD\ResphyImport\Resource\IOServices;

use DateTime;
use CLE;
use ADD\ResphyImport\Exception\FileIOException;

class ExamFileProcessor {
	private $examFileLoader;
	private $daysAllowed = 5;
	private $storeExamFile;
	private $daysAllowedIgnored;
	private $pdfTargetPath;
	private $processedPath;
	private $filesCopied = false;

    /**
     * @return bool
     */
    public function areFilesCopied()
    {
        return $this->filesCopied;
    }

    /**
     * @param bool $filesCopied
     */
    public function setFilesCopied($filesCopied)
    {
        $this->filesCopied = $filesCopied;
    }

	function __construct($examFileLoader,$storeExamFile, DateTime $now, $pdfTargetPath, $processedPath) {
		$this->examFileLoader = $examFileLoader;
		$this->storeExamFile = $storeExamFile;
		$this->now = $now;
		$this->pdfTargetPath = $pdfTargetPath;
		$this->processedPath = $processedPath;
	}
	function process($examFile) {
		$log = CLE::log($this);
		$this->examFileLoader->load($examFile);
		$imported = $this->storeExamFile->isExamFileImported($examFile);
				
		//load file into $examFile
		//if exam file is new -> load and store
		if(!$imported) {
			$log->info("Create new exam ".$examFile->getFileName());
			
			$this->storeExamFile->store($examFile);
			
			$this->copyPdfFileToSystem($examFile);
			$this->moveProcessedFilesFromBasePath("created", $examFile);
			return;
		}
		
		$currentDt = DateTime::createFromFormat("d/m/Y H:i",$examFile->getDateTimeOfTests());
		$interval = $currentDt->diff($this->now);
		$days = $interval->format('%a');

        // if it is X days since exam date/time of previously imported file
        //   update existing exam file
        //   move file(s) into 'imported' directory
        // else
        //   move files into 'ignored' directory
		if($this->isDaysAllowedIgnored() || intval($days)<$this->daysAllowed) {
			$log->info("Updating pre-existing tests within allowed days ($days) ".$examFile->getFileName());
				
			$this->storeExamFile->update($examFile,$imported);
			$this->copyPdfFileToSystem($examFile);
			$this->moveProcessedFilesFromBasePath("updated", $examFile);
		}
		else {
			$log->info("Ignoring files with pre-existing tests older than allowed days ($days) ".$examFile->getFileName
                ());
				
			$this->moveProcessedFilesFromBasePath("outofdate", $examFile);
		}

	}
	function makeProcessedDirectory($targetDir) {
		$fsystem = CLE::sys()->getService(CoreServices::FILESYSTEM);
		
		$targetPath = $this->getProcessedPath($targetDir);
		if(!$fsystem->exists($targetPath)) {
			$fsystem->mkdir($targetPath);
		}
		return $targetPath;
	}
	function getProcessedPath($targetDir) {
		return $this->processedPath."/$targetDir/".$this->now->format("Y/m/d");
	}
	function moveProcessedFilesFromBasePath($targetDir,$examFile) {
		$log = CLE::log($this);	
		$fmanager = CLE::sys()->getService(IOServices::FILEMANAGER);
		
		$filePath = $examFile->getFile()->getPathname();
		$targetPath = $this->makeProcessedDirectory($targetDir);
		
		$wcardFileName = str_replace(".TXT",".*",$filePath);
        $wcardFileName = str_replace(" ","\ ",addslashes($wcardFileName));
        $cmd = $this->areFilesCopied() ? "cp" : "mv";
        try {
            \ADD_Tool_Command::executeWithException("$cmd $wcardFileName $targetPath/");
        }
        catch(\ADD_CommandReturnValueException $ex) {
            throw new SystemCommandException($ex->getMessage());
        }

        //$escapedSourceFileName = str_replace(" ","\ ",addslashes($examFile->getSourcePdfFileName()));
        //exec("mv $wcardFileName $targetPath/");
		//$fmanager->move($wcardFileName,$targetPath."/");
		$log->info(($this->areFilesCopied() ? "Copy" : "Move")." files $wcardFileName to ".$targetPath."/");
	}
	function isDaysAllowedIgnored() {
		return $this->daysAllowedIgnored;
	}
	
	function setDaysAllowedIgnored($daysAllowedIgnored) {
		$this->daysAllowedIgnored = $daysAllowedIgnored;
	}
	
	
	function copyPdfFileToSystem($examFile) {
		if(!$examFile->getSourcePdfFileName()) return;
		$filesystem = CLE::sys()->getService(CoreServices::FILESYSTEM);
		$firstChar = substr($examFile->getTargetPdfFileName(),0,1);
		$secondChar = substr($examFile->getTargetPdfFileName(),1,1);
		$pdfTargetPath = $this->pdfTargetPath."/".$firstChar."/".$secondChar;
		$targetFileName = $pdfTargetPath."/".$examFile->getTargetPdfFileName();
        if(!file_exists($examFile->getSourcePdfFileName())) {
			throw new FileIOException("Source PDF file '".$examFile->getSourcePdfFileName()."' does not exist");
		}
        $escapedSourceFileName = str_replace(" ","\ ",addslashes($examFile->getSourcePdfFileName()));

        if(!is_dir($pdfTargetPath)) {
			try {
			    $filesystem->mkdir($pdfTargetPath);
            }
            catch(\Exception $ex) {
			    throw new SystemUserException("Unable to create directory ".$pdfTargetPath,$ex->getCode(),$ex);
            }
		}
		$cmd = "cp ".$escapedSourceFileName." $targetFileName";

		exec($cmd);
		if(!file_exists($targetFileName)) {
			throw new FileIOException("PDF file '".$targetFileName."' was not written");
		}
		
		CLE::log($this)->info("Copy PDF file from ".$examFile->getSourcePdfFileName()." to ".$targetFileName);
	}
}