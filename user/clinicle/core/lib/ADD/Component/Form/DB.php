<?php
/**
 * This class manages forms. It provides methods to load tab-delimited form definition files and convert them into Drupal forms
 * To set-up at file name is provided to the constructor. To generate the Drupal form structure, the
 * generateForm() method is used.
 * @author Rob Free
 * @version 0.1
 * @package ADD_Form
 */

class ADD_Component_Form_DB extends ADD_Component_Form {
	protected $_fileName = null;
	private $_dbForm = null;
	
	function getFileName() {
		return $this->_fileName;
	}
	function setFileName($fileName) {
		$this->_fileName = $fileName;
	}
	
	function setup() {
		if ($this->isSetup()) return;
                $page = $this->_page;
            
		$add = ADD::sys();
		$edb = ADD::db('encounter');
		$dbForm = $edb->getEncounterFormWithType($page->getSetting('encounter'));
        if (!$dbForm) {
            throw new ADD_FormDoesNotExistException("Could not setup encounter form ".$page->getSetting('encounter')." as it does not exist in the database.");
        }
		
		$this->setDbForm($dbForm);
		$this->_initFields($page);
                $this->setSetup(true);
	}
	
	function setDbForm($dbForm) {
		$this->_dbForm = $dbForm;
	}
	
	function getDbForm() {
		return $this->_dbForm;
	}

	/**
	 * Prepare Form object with various child arrays and output the top level field names
	 * @return array $topLevelFieldNames
	 */
	private function _initFields() {
            $page = $this->_page;

            $dbForm = $this->getDbForm();
            $edb = ADD::db('encounter');

            $dbFields = $edb->getObservationFieldsInEncounterForm($dbForm['id'],true);
			
			$registry = ADD::sys()->getService(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY);
			$ff = $registry->getFactory(\ClinicLE\System\Factory\SystemFactories::DB_FIELD);
            if ($dbFields) {
				foreach($dbFields as $dbField) {
					$field = $ff->newInstance($dbField);
					$this->addField($field);
				}
				$this->finaliseForm();
            }
            $this->setFieldsInit(true);
            $this->prepareChildrenAndTopLevelFields();
	}
}
?>