<?php

use Behat\Behat\Context\ClosuredContextInterface, Behat\Behat\Context\TranslatedContextInterface, Behat\Behat\Context\BehatContext, Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode, Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class AddContext extends BehatContext {
	public $patient = null;
	public $page = null;

	/**
	 * Initializes context.
	 * Every scenario gets it's own context object.
	 *
	 * @param array $parameters context parameters (set them up through behat.yml)
	 */
	public function __construct(array $parameters) {
		$add = ADD::sys();
		$add -> initModules();
		// Initialize your context here
	}

	/**
	 * @Given /^a patient is selected called \'([^\']*)\' \'([^\']*)\' with id \'([^\']*)\' and NHS no \'([^\']*)\' in the database$/
	 */
	public function iHaveAPatientCalledWithIdAndNhsNoInTheDatabase($fname, $lname, $id, $nhsNo) {
		$add = ADD::sys();
		$this -> resetScreen();
		$pdb = $add -> db('patient');
		$edb = $add -> db('encounter');
		$p = $pdb -> getPatientWithIdentifier($id);
		if ($p) {
			$p = $p->toArray(BasePeer::TYPE_FIELDNAME);
			$edb -> purgePatientEncounters($p['id']);
			$pdb -> purgePatient($p['id']);

		}
		$p = $pdb -> createPatient(array("first_name" => $fname, "last_name" => $lname, "identifier" => $id, "nhs_no" => $nhsNo));
		$this -> patient = $p;
		if (!is_array($p)) $p = $p -> toArray(BasePeer::TYPE_FIELDNAME);

		assertEquals($fname, $p['first_name']);
		assertEquals($lname, $p['last_name']);
		assertEquals($id, $p['identifier']);
		assertEquals($nhsNo, $p['nhs_no']);
		$add -> setPatientId($p['id']);
	}

	/**
	 * @Given /^there is no page encounter already present$/
	 */
	public function thereIsNoPageEncounterAlreadyPresent() {
		$add = ADD::sys();
		$edb = $add -> db('encounter');
		$edb -> purgePatientEncounters($add -> getPatientId());
	}

	/**
	 * @When /^I attempt to add the patient to the selected page$/
	 */
	public function iAttemptToAddThePatientToTheSelectedPage() {
		$add = ADD::sys();
		$add -> setParam(ADD_Page::T, ADD_Util::token());
		$this -> page -> createContent();
	}

	/**
	 * @Then /^I view the page$/
	 */
	public function iViewThePage() {
		return $this -> iAttemptToAddThePatientToTheSelectedPage();
	}

	/**
	 * @Then /^a single domain should be present$/
	 */
	public function aSingleDomainShouldBePresent() {
		$add = ADD::sys();
		$edb = $add -> db('encounter');
		$page = $this -> page;
		$encType = $page -> getSetting('encounter');
		$encs = $edb -> getEncountersWithPatientAndType($add -> getPatientId(), $encType);
		assertEquals(1, count($encs));
	}

	/**
	 * @Then /^a \'([^\']*)\' message is present$/
	 */
	public function aMessageIsPresent($message) {
		$add = ADD::sys();
		if ($this -> page) {
			assertEquals($this -> page -> getMessages(), array($message));
		}
	}
	
	/**
	 * @Then /^a \'([^\']*)\' warning is present$/
	 */
	public function aWarningIsPresent($warning) {
		$add = ADD::sys();
		if ($this -> page) {
			assertEquals($this -> page -> getWarnings(), array($warning));
		}
	}

	/**
	 * @Given /^I have \'([^\']*)\' permission in the selected page$/
	 */
	public function iHavePermissionInTheSelectedPage($perm) {
		$add = ADD::sys();
		$this -> page -> setPermission($perm, true);
	}

	/**
	 * @Given /^I have no permission in the selected page$/
	 */
	public function iHaveNoPermissionInTheSelectedPage() {
		$add = ADD::sys();
		$this -> page -> setPermissions(array());
	}

	/**
	 * @Then /^a "([^"]*)" error is present$/
	 */
	public function aErrorIsPresent($error) {
		$add = ADD::sys();
		assertEquals($add -> getErrors(), array($error));
	}

	/**
	 * @Given /^no patient is selected$/
	 */
	public function noPatientIsSelected() {
		$add = ADD::sys();
		$add -> setErrors(array());
		$add -> setWarnings(array());
		$add -> setMessages(array());
		ADD::sys() -> setPatientId(null);
	}

	/**
	 * @Then /^there are no \'([^\']*)\' domains present$/
	 */
	public function thereAreNoDomainsPresent() {
		$add = ADD::sys();
		$edb = $add -> db('encounter');
		$page = $this -> page;
		$encType = $page -> getSetting('encounter');
		$encs = $edb -> getEncountersWithPatientAndType($add -> getPatientId(), $encType);
		assertEquals(0, count($encs));
	}

	/**
	 * @Then /^the options output are "([^"]*)"$/
	 */
	public function theOptionsOutputAre($expOut) {
		$add = ADD::sys();
		$scr = $add -> getScreen();
		$opts = $this -> page -> createOptions();
		assertEquals(strip_tags($opts -> output()), $expOut);
	}

	/**
	 * @Given /^the page selected is \'([^\']*)\' in the module \'([^\']*)\'$/
	 */
	public function thePageSelectedIsInTheModule($pageName, $modName) {
		$add = ADD::sys();
		$page = $add -> getPageInModule($modName, $pageName);
		$this -> page = $page;
	}

	/**
	 * @Then /^there are no errors present$/
	 */
	public function thereAreNoErrorsPresent() {
		$add = ADD::sys();
		assertEquals(null, $add -> getScreen() -> getErrors());
	}

	/**
	 * @Then /^a single sub-encounter \'([^\']*)\' should be present$/
	 */
	public function aSingleSubEncounterShouldBePresent($cpageName) {
		$add = ADD::sys();
		$edb = $add -> db('encounter');
		$page = $this -> page;
		$encType = $page -> getSetting('encounter');
		$encs = $edb -> getEncountersWithPatientAndType($add -> getPatientId(), $encType);
		$cpage = $page -> getChildPage($cpageName);
		if (!$cpage)
			throw new Exception("Child page '$cpageName' not found in page '{$page->getName()}'");
		$cencType = $cpage -> getSetting('encounter');
		$cencs = $edb -> getChildEncountersWithType($encs[0], $cencType);
		assertEquals(1, count($cencs));
	}

	/**
	 * @Given /^I have \'([^\']*)\' permission in the \'([^\']*)\' page$/
	 */
	public function iHavePermissionInThePage($perm, $pageName) {
		$mod = $this -> page -> getModule();
		$page = $mod -> getPage($pageName);
		if (!$page)
			throw new Exception("Page '$pageName' not found in module '{$mod->getName()}'");
		$page -> setPermission($perm, true);
	}

	/**
	 * @Given /^I get the page with URL \'([^\']*)\'$/
	 */
	public function iGetThePageWithUrl($urlPath) {
		$add = ADD::sys();
		$scr = $add -> getScreen();
		$scr -> createOutput(array("method" => "GET", "path" => $urlPath, "values" => array()));
		$this -> page = $scr -> getCurrentPage($urlPath);
	}

	/**
	 * @Then /^the content is "([^"]*)"$/
	 */
	public function theContentIs($expContent) {
		$add = ADD::sys();
		assertEquals($expContent,strip_tags($this->page -> html()->toHtml()));
	}

	/**
	 * @Given /^I have \'([^\']*)\' permission in the \'([^\']*)\' page of the \'([^\']*)\' module$/
	 */
	public function iHavePermissionInThePageOfTheModule($perm, $pageName, $modName) {
		$add = ADD::sys();
		$page = $add -> getPageInModule($modName, $pageName);
		if (!$page)
			throw new Exception("Page '$pageName' not found in module '{$mod->getName()}'");
		$page -> setPermission($perm, true);
	}

	public function resetScreen() {
		$add = ADD::sys();
		if ($this -> page) {
			$this -> page -> setErrors(array());
			$this -> page -> setWarnings(array());
			$this -> page -> setMessages(array());
		}
		ADD::sys() -> setPatientId(null);
	}

	/**
	 * @Then /^the menu option is \'([^\']*)\'$/
	 */
	public function theMenuOptionIs($expOptions) {
		$options = $this -> page -> createOptions();
		assertEquals($expOptions, strip_tags($options -> toHtml()));
	}

	/**
	 * @When /^I submit the page$/
	 */
	public function iSubmitThePage() {
		$add = ADD::sys();
		$token = ADD_Util::token();
		$add->setParam(ADD_Page::T,$token);
		$values = array('__token'=>$token,'__patient'=>$add->getPatientId());
		$data = $this -> page -> submitForm($values);
		print_r($data);
		print_r($this->page->getWarnings());
		
	}

	/**
	 * @Then /^the sub-page \'([^\']*)\' observation \'([^\']*)\' is \'([^\']*)\'$/
	 */
	public function theSubPageObservationIs($cpageName, $obsName, $obsValue) {
		$ppage = $this -> page -> getParentPage();
		$cpage = $ppage -> getChildPage($cpageName);
		$cpage->getComponent('Encounter')->reset();
		list($cenc, $cobs) = $cpage -> retrieveEncounter();
		assertEquals($obsValue, ADD_Util::getArrayValue($obsName, $cobs));
	}
	
	 /**
     * @Then /^I wait \'([^\']*)\' seconds$/
     */
    public function iWaitSeconds($no)
    {
        sleep($no);
    }
    
    /**
     * @Given /^I have added them to the \'([^\']*)\' domain$/
     */
    public function iHaveAddedThemToTheDomain($domain)
    {
    	$this->page = ADD::sys()->getPageInModule($domain,$domain);
    	$this->page->setPermission('create',true);
        $this->iViewThePage();
    }
    
    /**
     * @Then /^I should get a warning:$/
     */
    public function iShouldGetAWarning(PyStringNode $warnings)
    {
        $add = ADD::sys();
		if ($this -> page) {
			assertEquals($warnings->getLines(),$this -> page -> getWarnings() );
		}
    }
    
    /**
     * @Then /^the content is like "([^"]*)"$/
     */
    public function theContentIsLike($expContent)
    {
        $add = ADD::sys();
		assertRegExp($expContent,strip_tags($this->page -> html()->toHtml()));
    }
    
    /**
     * @Given /^I wait until the sub-page \'([^\']*)\' observation \'([^\']*)\' is \'([^\']*)\' for up to \'([^\']*)\' seconds$/
     */
    public function iWaitUntilTheSubPageObservationIsForUpToSeconds($cpageName, $obsName, $obsValue, $maxTime)
    {
    	$notFound = true;
    	$currTime = 0;
    	$log = ADD::log($this);
    	while($notFound) {
	        $ppage = $this -> page -> getParentPage();
			$cpage = $ppage -> getChildPage($cpageName);
			$cec = $cpage->getComponent('Encounter');
			$cec->reset();
			list($cenc, $cobs) = $cec -> retrieveEncounter($cpage);
			$log->info("cobs:".json_encode($cobs));
			$log->info("$obsName:".ADD_Util::getArrayValue($obsName, $cobs));
			$log->info("civi_log:".ADD_Util::getArrayValue('civi_log', $cobs));
			if (ADD_Util::getArrayValue($obsName, $cobs)==$obsValue) $notFound=false;
			sleep(1);
			$currTime+=1;
			if ($currTime>=$maxTime) throw new Exception("Test not passed within alloted time of $maxTime seconds");
		}	
    }
    
    /**
     * @Then /^I should get a message:$/
     */
    public function iShouldGetAMessage(PyStringNode $messages)
    {
        $add = ADD::sys();
		if ($this -> page) {
			assertEquals($messages->getLines(),$this -> page -> getMessages() );
		}
    }
    
    /**
     * @Then /^I submit data to the \'([^\']*)\' page$/
     */
    public function iSubmitDataToThePage($arg1, PyStringNode $string)
    {
        $add = ADD::sys();
		$token = ADD_Util::token();
		$add->setParam(ADD_Page::T,$token);
		$values = array('__token'=>$token,'__patient'=>$add->getPatientId());
		$posts = $string->getLines();
		foreach($posts as $post) {
			list($key,$value) = explode("=",$post);
			$values[$key]=$value;
		}
		ADD::log()->info("values:".print_r($values,1));
		$data = $this -> page -> submitForm($values);
    }

    /**
     * @Given /^I run the recruit patient script$/
     */
    public function iRunTheRecruitPatientScript()
    {
    	$log = ADD::log();	
    	$log->info("exec: php bin/recruitPatient.php ".ADD::sys()->getPatientId());
        exec("php bin/recruitPatient.php ".ADD::sys()->getPatientId());
    }
}
