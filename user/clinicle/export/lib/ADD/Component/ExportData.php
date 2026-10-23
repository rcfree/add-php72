<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author rcf8
 */


class ADD_Component_ExportData extends ADD_Component {
	function getComponentList() {
		return array(
			'Form_Simple',
		);
	} 
	public function contentType(&$contentType) {
		if(!ADD::sys()->getParam('export')) {
			$contentType="Html";
			return;
		}
		$format = ADD::sys()->getParam('format');
		if($format=="csv") {
			$contentType="CSV";
			return;
		}
		$contentType="Html";
	}
	public function menuPath(&$menuPath) {
		$page = $this->_page;
		$menuPath=$page->getModule()->getName()."/".$page->getName();
	}
	public function displayAccess() {
		if(!$this->_page->getPermission('export')) throw new ADD_NoPermissionToAccessException("You do not have access to the ".$this->getSetting('title')." page");
	}
	
	public function expectedPermissions(&$expPerms) {
		$expPerms=array("export");
	}
	public function output() {
		if(!ADD::sys()->getParam('export')) return;
		
		$this->displayAccess();
		$content = ADD::web()->getContent();
		$data = $this->_obtainData();
		$content->setData($data);
	}
	public function setup() {
	    $services = ADD::sys()->getServiceContainer();
        $services['ConsentRecord']=new ADD_Database_Consent();

		$page = $this->_page;
		$form = $page->getComponent('Form');
		$form->setFormMethod("GET");
        $form->setActionFromPage($this->_page);
        $form->setIgnoreNg(true);

		$ff = new ADD_FieldFactory_RowData();
		$formats = array("csv"=>"Comma-separated values","html"=>"HTML");

		$fieldData[]=array("Field"=>"encTypes","Title"=>"Encounter type (use % as wild-card, comma to separate encounter types)","Type"=>"Text");
		$fieldData[]=array("Field"=>"rowPerPatient","Title"=>"Row per patient","Type"=>"Checkbox");
		$fieldData[]=array("Field"=>"format","Title"=>"Export format","Type"=>"Dropdown","Options"=>$formats);
		$fieldData[]=array("Field"=>"memberOf","Title"=>"Must be member of encounter type (e.g. dac)","Type"=>"Text");
		$fieldData[]=array("Field"=>"identifierList","Title"=>"List of S numbers (space separated, use = to remove identifier)","Type"=>"Text");
		$fieldData[]=array("Field"=>"researchConsent","Title"=>"Research consented? (e.g. research DB, DAC or advanced COPD)","Type"=>"Checkbox");
        $fieldData[]=array("Field"=>"recruitmentStatus","Title"=>"Recruitment status? (e.g. available, do not contact)","Type"=>"Checkbox");

                $fieldData[]=array("Field"=>"removeMemo","Title"=>"Remove memo fields","Type"=>"Checkbox");
        $fieldData[]=array("Field"=>"ilhIdentifier","Title"=>"Include ILH identifier","Type"=>"Checkbox");
        $fieldData[]=array("Field"=>"namesOnly","Title"=>"Use field names only","Type"=>"Checkbox");
        $fieldData[]=array("Field"=>"sepCheckValues","Title"=>"Separate checkbox values","Type"=>"Checkbox");
        $fieldData[]=array("Field"=>"valuesOnly","Title"=>"Only output field values","Type"=>"Checkbox");


		foreach($fieldData as $fieldItem) {
			$fieldName = ADD_Util::val('Field',$fieldItem);
			if(!ADD_Util::isEmpty($this->getSetting($fieldName))) {
			    $fieldItem['Type']='Hidden';
            }

			$field = $ff->newInstance($fieldItem);
			$form->addField($field);
		}
		$form->finaliseForm();
	}

	public function beforePopulate() {
		$page = $this->_page;
		$settings = $page->setData($this->getAllSettings());
	}
	public function afterGenerate() {
		$content = ADD::web()->getContent();

		$content->addTextElement("button","<i class='icon-download icon-white'></i>&nbsp;Export data",array("name"=>"export","class"=>"btn-primary btn","value"=>"1","cle-nong"=>"1"));
	}
	public function generate() {
		$content = ADD::web()->getContent();
		
		if(!ADD::sys()->getParam('export')) {
			return parent::generate();
		}
		$data = $this->_obtainData();
		$content->addHtml($data);
	}
	
	public function _obtainData() {
		$add = ADD::sys();
		$params = ADD::sys()->getParams();

		$params['encTypes']=explode(",",ADD::sys()->getParam('encTypes'));
		$gd = new ADD_Export_GenerateData($params, $add->getModules());
		$outputFile = $gd->outputFile();
		return file_get_contents($outputFile);
	}

	public function title(&$title) {
		$title = $this->_page->getSetting('title');
	}
	
	public function breadcrumb(&$breadcrumb) {
		$breadcrumb[]=$this->_page->getModule()->getSetting('title');
		$breadcrumb[]=$this->_page->getSetting('title');
	}
}
