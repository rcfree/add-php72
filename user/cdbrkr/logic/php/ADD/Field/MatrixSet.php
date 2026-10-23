<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Field_MatrixSet extends ADD_Field_Grid  {

	function addMarkup($page) {
		$this->setupInfo($page);
		$this->setSetting('use_parent',true);

        $container = new \ClinicLE\Client\Container\Container($this->getName(),"MatrixSetController");
        $table = new \ClinicLE\Client\Container\Table($this->getTitle(),"control.data.grid","row");
        $container->appendAttrs(array("cle-dataurl"=>"/api/dictionaryset","cle-lookupdict"=>$this->getSetting('lookupdict'),"cle-groupdict"=>$this->getSetting('groupdict')));
        $table->setTableClass("table table-condensed");

		$table->setCountHidden(true);
		$this->_addGridProperties($container,$table);
		$container->putProperty("pim",$this->getSetting('check_select_page'));

		$table->setHeadings(array());
		
        $cell = new \Codebreaker\Logic\MatrixSetCell($this->getSetting('columns'),$this->getSetting('cell_pattern',"cell.description"));
		
		$table->addCell($cell);

        $container->addToContent($table);

		$page->html()->addHtml($container->createHtml());
	}
	function getDataUrl() {
		return "/api/dictionaryset";
	}
}
