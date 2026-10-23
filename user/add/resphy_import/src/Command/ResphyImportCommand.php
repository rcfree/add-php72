<?php
namespace ADD\ResphyImport\Command;

use ADD\ResphyImport\ExamFile;
use ADD\ResphyImport\ExamFileLoader;
use ADD\ResphyImport\Exception\InvalidFileException;
use ADD\ResphyImport\Resource\RespPhysService;
use ADD\ResphyImport\Resource\StoreExamFile;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Util;
use ClinicLE\System\Service\CoreServices;
use ADD\ResphyImport\Resource\FileManager;
use ADD\ResphyImport\Resource\IOServices;
use ADD\ResphyImport\ExamFileProcessor;
use ADD\ResphyImport\Exception\SystemCommandException;
use CLE;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wrep\Daemonizable\Command\EndlessCommand;

class ResphyImportCommand extends Command {
	private $examPath;
	private $processPath;
	private $pdfTargetPath;
	private $prevProcessed;
	private $fileInPath;
	private $problemFiles = array();
	protected function configure()
	{
		$this->setName('resphy-import')
			->setDescription('Import respiratory physiology files')
			->setDefinition(array(
                new InputOption("date-since", "d", InputOption::VALUE_OPTIONAL, "include files since a particular date"),
                new InputOption("all", "a", InputOption::VALUE_NONE, "Include all files in source directory"),
                new InputOption("prev-processed-dir", "p", InputOption::VALUE_OPTIONAL, "Sub-path to previously processed files"),
                new InputOption("allowed-days-ignored","i",InputOption::VALUE_NONE,"Ignore allowable days setting and include all test files"),
                new InputOption("copy","c",InputOption::VALUE_NONE,"Copy files rather than move them"),
            ));
		if(is_a($this,EndlessCommand::class)) {
            $this->setTimeout(10);
        }
	}
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
        $log = CLE::log($this);
        $config = CLE::sys()->getConfig()->getSection("ResphyImport");
        if(!$config) throw new \Exception("ResphyImport config section missing");

        $this->examPath = $config->assertSetting('exam_path');
        $this->processPath = $config->assertSetting('process_path');
        $this->pdfTargetPath = $config->assertSetting('pdf_target_path');
        $log->running();
        $this->prevProcessed = $input->getOption("prev-processed-dir");

        $this->fileInPath = $this->prevProcessed ? $this->processPath."/".$this->prevProcessed : $this->examPath;
        $log->info("Begin watching files in ".$this->fileInPath);

        if(is_a($this,EndlessCommand::class)) {
        	$this->throwExceptionOnShutdown();
        }
	}

    protected function execute(InputInterface $input, OutputInterface $output) {
		$log = CLE::log($this);
        $serviceContainer = CLE::sys()->getServiceContainer();
		$serviceContainer[IOServices::FILEMANAGER] = new FileManager();

		$nowDt = new DateTime();
		
		$efl = new ExamFileLoader($this->examPath, $nowDt);
		$patientLookup = \ADD_Util::createPMI();
		$matchResultFactory = new \ADD_PatientImport_MatchResultFactory();
		$pmiMatcher = new \ADD_PatientImport_PmiMatcher($patientLookup,$matchResultFactory);
		$respPhysService = new RespPhysService();
		$patientService = new \ADD_Data_PatientService();
		$sef = new StoreExamFile($pmiMatcher, $patientService, $respPhysService);
		
		$ffinder = CLE::sys()->getService(CoreServices::FILEFINDER);
		chdir($this->examPath);
		$log->info("chdir:$this->examPath");
		$this->prevProcessed = $input->getOption("prev-processed-dir");
		$allDates = $input->getOption("all");
		$dateRange = $input->getOption("date-since") ? $input->getOption("date-since") : "since today";

		$ffinder->name("*.TXT");


		if(!$this->prevProcessed) {
			$ffinder->depth("==0");
		}
        $ffinder->in($this->fileInPath);
		if(!$allDates) {
			$ffinder->date($dateRange);
		}
		
		$log->info("files:" . print_r($ffinder,1));
		$ffinder->files();
        $fileCount = count($ffinder);

        if($fileCount==0) {
        	return;
        }
        CLE::svc(CoreServices::DATABASE_MANAGER)->_db=array();

        $fileProcessor = new ExamFileProcessor($efl,$sef,$nowDt, $this->pdfTargetPath, $this->processPath);
        $fileProcessor->setFilesCopied($input->getOption("copy"));
		$fileProcessor->setDaysAllowedIgnored($input->getOption("allowed-days-ignored"));

		$fcount=0;
		foreach($ffinder as $file) {
			if(preg_match("/".preg_quote($fileProcessor->getProcessedPath(""),"/")."/",$file->getPath())) {
				continue;
			}
            if(!$serviceContainer[IOServices::FILEMANAGER]->isReadable($file)) {
				continue;
            }
			$examFile = new ExamFile($file);
			if(in_array($examFile->getFileName(),$this->problemFiles)) {
				//$log->warn("Ignore previous problem file:".$examFile->getFileName());
				continue;
			}
			try {
				CLE::db()->beginTxn();
				$fileProcessor->process($examFile);
				CLE::db()->commitTxn();
			}
			catch(SystemCommandException $ex) {
				CLE::db()->rollbackTxn();
				$log->error("File ".$examFile->getFileName().": ".$ex->getMessage(). " (Files not moved due to SystemCommandException). Ignore on next pass.");
                $this->problemFiles[]=$examFile->getFileName();
			}
			catch(\Exception $ex) {
				CLE::db()->rollbackTxn();
                $log->error("File ".$examFile->getFileName().": ".$ex->getMessage(). " (Files not processed due to "
					.$ex->getMessage().")");
				try {
					$fileProcessor->moveProcessedFilesFromBasePath("error",$examFile);
				}
				catch(SystemCommandException $ex) {
					$log->error("File ".$examFile->getFileName().": ".$ex->getMessage(). " (Files not moved due to SystemCommandException).  Ignore on next pass.");
					$this->problemFiles[]=$examFile->getFileName();
				}
			}
			$fcount++;
		}
   if($fcount>0) {
        $log->info($fcount. " ".Util::pluralize($fcount,"file")." processed in {$this->fileInPath}");
        }
    }
	
}
?>
