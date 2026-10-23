<?php
namespace ClinicLE\Page;
use ClinicLE\Form\Form;
use ClinicLE\Form\Builder\BuilderInterface;
use ClinicLE\Form\FormContainerInterface;
use ClinicLE\Form\FormContainerTrait;

class FormPage extends StandardPage implements FormContainerInterface {
    use FormContainerTrait;
    protected $data=array();
    private $formBuilder;
	
    function getFormBuilder() {
	return $this->formBuilder;
    }
    function setFormBuilder(BuilderInterface $formBuilder) {
	$this->formBuilder = $formBuilder;
    }
    function setData($data) {
	$this->data = $data;
    }
    function getData() {
	return $this->data;
    }

}	
?>
