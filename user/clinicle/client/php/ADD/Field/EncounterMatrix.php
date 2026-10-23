<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\Client\Container\Cell\MatrixCell;

class ADD_Field_EncounterMatrix extends ADD_Field_EncounterGrid {
    function _addGridProperties(\ClinicLE\Client\Container\Container $container, \ClinicLE\Client\Container\Table
    $table) {
	    parent::_addGridProperties($container, $table);
        $container->putProperty("custom_pattern",htmlentities($this->getSetting('custom_pattern','cell.value')));
        if($this->getSetting('map_as_date')) {
            $container->putProperty("map_as_date",$this->getSetting('map_as_date'));
        }
    }
	function addMarkup($page) {
		$this->setupInfo($page);

        $container = new \ClinicLE\Client\Container\Container($this->getName(),"EncounterGridController");

        $matrixAttrs = array("cle-xfield"=>$this->assertSetting('xfield'),"cle-yfield"=>$this->assertSetting('yfield'),"cle-valuefield"=>$this->assertSetting('value_field'));
        $container->appendAttrs($matrixAttrs);
        $container->putProperty("module",$page->getModule()->getName());

        $grid = new \ClinicLE\Client\Container\Table($this->getTitle(),"control.data.grid","row");
        $grid->setTableClass("table-bordered");
        $grid->setNoHeadings(true);
        $grid->setCountType(\ClinicLE\Client\Container\Table::COUNT_COLUMNS);
		//$beforeActions = $this->_instantiateRowActions("before_column_actions");
		//$afterActions = $this->_instantiateRowActions("after_column_actions");
		$cellActions = $this->_instantiateCellActions("cell_actions");

		$this->_addGridProperties($container,$grid);
		
		$this->addFilters($container);
        $grid->addCell(new MatrixCell($this->getSetting('headcol_width','10em'),$this->getSetting('datacol_width',
            '5em'), $cellActions));
		//$grid->setNoCount(true);

        /*
		foreach($beforeActions as $action) {
			$gridHelper->addActionBeforeColumn($action);
		}
		
		foreach($afterActions as $action) {
			$gridHelper->addActionAfterColumn($action);
		}
		 */
        $content = $container->getContent();
        $content->addHtml($grid->createHtml());
		$page->html()->addHtml($container->createHtml($page));
		
	}
	function getDataUrl() {
		return "/api/matrix";
	}
	function _instantiateCellActions() {
		$cellActionsSetting = $this->getSetting("cell_actions");
		if(!$cellActionsSetting) return array();
		$cellActionList = explode(";",$cellActionsSetting);
		
		$cellActions = array();
		foreach($cellActionList as $cellAction) {
			$cellActions[]=$this->_createCellAction($cellAction);
		}
		return $cellActions;
	}
	
	function _createCellAction($cellActionName) {
		$gridAction = ADD_Util::loadClass("ADD_Client_Action_".$cellActionName,$this->_usePage);
		if($gridAction instanceof ADD_Client_Action_GridFieldContainerInterface) {
			$gridAction->setGridField($this);
		}
		return $gridAction;
	}
}
