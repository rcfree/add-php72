<?php
/**
 * This class manages forms. It provides methods to load tab-delimited form definition files and convert them into Drupal forms
 * To set-up at file name is provided to the constructor. To generate the Drupal form structure, the
 * generateForm() method is used.
 * @author Rob Free
 * @version 0.1
 * @package ADD_Form
 */



class ADD_Component_Form_Simple extends ADD_Component_Form {
	protected $_formMethod="POST";
	protected $_formAction;
	function getFormMethod() {
		return $this->_formMethod;
	}
	function setFormMethod($method) {
		$this->_formMethod = $method;
	}
	function setActionFromPage(ADD_Page $page) {
		$this->_formAction = "/".$page->createMenuPath();
	}
	
	function beforeSubmit(&$values,$origValues = NULL) {
	}
	function generate() {
		$this->finaliseForm();
		parent::generate();
	}
}
