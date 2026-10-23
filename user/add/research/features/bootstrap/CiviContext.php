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
class CiviContext extends BehatContext {
	private $ca = null;
	public function __construct(array $parameters) {
		
	}	
	 /**
     * @Then /^the contact exists in CiviCRM$/
     */
    public function theContactExistsInCivicrm()
    {
        $ca = $this->initCA();
        $patient = $this->getMainContext()->getSubcontext('add')->patient;
        list($contact,$count) = $ca->get('Contact',array(
			"custom_1220"=>$patient->getIdentifier()
		));
		assertEquals($patient->getLastName(),$contact['last_name']);
    }
    
    public function initCA() {
    	if(!$this->ca) {
	    	$page = ADD::sys()->getPageInModule('research','recruit');
			$url = $page->getSetting('RecruitPatient.civiUrl');
			$apiKey = $page->getSetting('RecruitPatient.apiKey');
			$siteKey = $page->getSetting('RecruitPatient.siteKey');
			
			$this->ca = new ADD_Tool_CiviAccess($url,$siteKey,$apiKey);
			print_r($this->ca);
		}
		return $this->ca;
	}
}
