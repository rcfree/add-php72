<?php
/**
 * Helps automate unit testing of pages in a module bundle based on the components used
 *
 * @author Rob Free
 * @category ADD
 * @package ModuleBundle
 */


class ADD_Test_ModuleTest {
    private $_moduleDependency = null;
    private $_showOutput = false;
    function __construct(ADD_Test_ModuleDependency $moduleDependency, $doxOutput=false, $quiet=false) {
		$this->_moduleDependency=$moduleDependency;
		$this->_doxOutput = $doxOutput;
		$this->_quiet = $quiet;
    }
    function runTests($pageName=null,$compName=null, $descendants=false, $showOutput=false, $filter=null) {
        $log = ADD::log();
        $mod = $this->_moduleDependency->getModule();
		$this->_showOutput=$showOutput;
		$pages = $pageName ? array($mod->getPage($pageName)) : $mod->getPages();
        if ($descendants && $pageName) {
        	$cpages = $mod->getPage($pageName)->getChildPages();
			$pages = array_merge($pages,$cpages);
		}
		
        foreach($pages as $page) {
			$pageTestCount = 0;
        	if (!$page) throw new Exception("Page not found in module");
                 
            putenv("ADD_PAGE_TEST=".$mod->getName().":".$page->getName());
		
            if ($page->hasComponent('Form') && get_class($page->getComponent('Form'))=="ADD_Component_Form_DB") {
				$page->getComponents()->add("Form_File");
            	$form = $page->getComponent('Form');
            	$form->getFieldContent()->setNotFinalise(true);
				
				$page->setupComponents();
                $ignoreTypes=array("Internal","DateTime","Memo","Checkbox","Date","TabContainer","RadioButtons","Fieldset","Markup","PageLink","DisplayText","Item","Dropdown","Text","YesNo","Tab","HorizContainer","Number","Icon","Tab","Checkboxes");
                foreach($form->getFields() as $field) {
                    if (in_array($field->getType(),$ignoreTypes)) continue;
                    if ($field->getLevel()=="DEL") continue;
                    putenv("ADD_FIELD_TEST=".$field->getName());
                    $pageTestCount+= $this->_runSpecificTests($page, "Field",array($field->getType()),"(".$field->getName().")");
                }
            }
            $compList = $compName ? array($compName) : $page->getSettingList('components'); 
            
            $pageTestCount += $this->_runSpecificTests($page, "Component",$compList, null,$filter);
			if(!$this->_quiet) $log->info("Run tests for page ".$page->getName()." (".$pageTestCount.")");
        }
        if($this->_doxOutput) {
			exec("cat /tmp/dox".$mod->getName()."/* > /tmp/doxreport",$output,$returnVar);
			print file_get_contents("/tmp/doxreport");
		}
    }

    function _findTestDirsInModules($classType,$classPath) {
		$testDirs = array();
        foreach($this->_moduleDependency->getModules() as $mod) {
			$testFile = $mod->getPath()."/test/$classType/$classPath";
			if (file_exists($testFile)) $testDirs[]=$testFile;
			
			$testFile = $mod->getPath()."/test/module/ADD/$classType/$classPath";
			if (file_exists($testFile)) $testDirs[]=$testFile;
        }
		return $testDirs;
    }
    function _findTestDirectories($classType,$className) {
        $classPath = str_replace("_","/",$className);
		
		$testDirs = array();
        $testFile = ADD::sys()->getPath()."/core/test/module/ADD/$classType/$classPath";
		if (file_exists($testFile)) $testDirs[]=$testFile;
		$modTestDirs = $this->_findTestDirsInModules($classType,$classPath);
		$testDirs=array_merge($testDirs,$modTestDirs);
		
		if (empty($testDirs)) {
			 ADD::log()->debug("Tests not found for $classType '$className' core or module");
			 return array();
		}
		
        return $testDirs;
    }
    function _runSpecificTests($page, $classType, $classNames, $extra="", $filter=null) {
        $log = ADD::log();
		$cwd = getcwd();
        $module = $this->_moduleDependency->getModule();
		chdir($module->getPath());
		
		$fileCount = 0;
		foreach($classNames as $className) {
            $testDirs = $this->_findTestDirectories($classType,$className);
			
            if (empty($testDirs)) {
				$log->debug("NO test files for $classType '$className'");
				continue;
			}
			
			$systemCommand = new ADD_Tool_Command();
			foreach($testDirs as $testDir) {
				$log->debug("Run test files for $classType '$className' in '$testDir' $extra");

				$doxfile = "/tmp/dox".$module->getName()."/".$page->getName()."_".$className;

				$opts = array("phpunit --no-globals-backup");
				if($filter) $opts[]="--filter=$filter";
				if($this->_doxOutput) $opts[]="--testdox-text=$doxfile";
				$opts[]="--bootstrap ".ADD::sys()->getPath()."/vendor/clinicle/manage/extra/phpunit-bootstrap.php";
				$opts[]=escapeshellarg($testDir);
				$log->info("Opts:".json_encode($opts));
					
				$output = $systemCommand->executeWithException($opts,$this->_showOutput,"Test failure for $classType in page '".$page->getName()."'");
				if(!$this->_showOutput) {
					$endOfOutput = array_slice($output,count($output)-2);
					$log->debug(implode(":",$endOfOutput));
				}
				
				$fileCount+=count(glob($testDir."/*.php"));
			}
        }
		chdir($cwd);
		return $fileCount;
    }

    function checkSyntax() {
            $mod = $this->_moduleDependency->getModule();
            if(file_exists($mod->getPath()."/php")) $libPath = $mod->getPath()."/php";
            if(file_exists($mod->getPath()."/lib")) $libPath = $mod->getPath()."/lib";
            if(!isset($libPath)) return;

            $files = array();
            $returnVar = null;
            $system = "find ".  escapeshellarg($libPath)." | grep .php";

            exec($system,$files,$returnVar);
            $syntCount=0;
            foreach($files as $file) {
                    if(is_dir($file)) continue;
                    include_once($file);
                    $syntCount++;
            }
            ADD::log()->info("Check syntax of ".$syntCount." " .ADD_Util::pluralize($syntCount,"PHP file")." in ".$libPath);
    }
}