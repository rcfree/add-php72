<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



class ADD_Field_Grid extends ADD_Field_Base {
	protected $stored = false;
	
	function setupInfo($page) {
		$add = ADD::sys();
		if($this->getSetting('no_title')) $this->setTitleHidden(true);
	}
	function getControllerName() {
	    return $this->getSetting("controller","EncounterGridController");
    }
	function generateTableData(\ClinicLE\Client\Container\Table $table, $page) {
		$add = ADD::sys();
		
		$colFieldNameList = $this->assertSetting('fields');
		$colFieldNames = explode(";",$colFieldNameList);

        foreach ($colFieldNames as $colFieldName) {
            $colTitle = ADD_Util::nameToLabel($colFieldName);

            $cell = new \ClinicLE\Client\Container\Cell\FieldCell($colTitle, $colFieldName);

            $table->addCell($cell);
        }
        /*
		foreach ($colFieldNames as $colFieldName) {
			$cell = null;
			
			$fieldText = "row.".$colFieldName;
			$cell = '<p ng-bind-html="'.$fieldText.'"></p>';
			
			$data[] = $cell;
		}
		if($this->getSetting('col_headings')) {
			$header = explode(";",$this->getSetting('col_headings'));
		}
        */
		//$rows=array($data);
		//return array($header, $rows);
	}
	
	function addMarkup($page) {
		$this->setupInfo($page);

        $container = new \ClinicLE\Client\Container\Container($this->getName(),$this->getControllerName());
        $container->putProperty("module",$page->getModule()->getName());

        $beforeRowActions = $this->_instantiateRowActions("before_row_actions");
		$afterRowActions = $this->_instantiateRowActions("after_row_actions");

        $grid = new \ClinicLE\Client\Container\Table($this->getTitle(),"control.data.grid","row");

        $this->addRowActions($beforeRowActions, $grid);
        $this->generateTableData($grid, $page);
        $this->addRowActions($afterRowActions, $grid);

        //$grid = new ADD_Content_Html_EncounterGridHelper($this->getName()."-app",$this->getTitle());
		$grid->setLoader(!$this->getSetting('loading_hidden'));

		$grid->setWidths(explode(";",$this->getSetting('col_widths','')));


        $content = $container->getContent();
        $this->_addGridProperties($container, $grid);

        if($this->getSetting("use_paging")) {
            $grid->setPaged(true);
            $container->putProperty("rows_per_page",$this->getSetting("rows_per_page",20));
            $container->putProperty("page_no",1);
        }

        $content->addHtml($grid->createHtml());

		$modal = new ADD_Content_Html_ModalHelper("",$this->getName()."-modal",array("style"=>"width:".$this->getSetting('modal_width','700')."px"));
		$mbody = $modal->getModalBody();
		$modal->addButton("<i class='icon-remove'></i>&nbsp;Close",array("ng-click"=>"closeItem()"));
		
		$modal->addButton("<i class='icon-ok icon-white'></i>&nbsp;Save item",array("ng-click"=>"saveItem()","class"=>"btn-primary save-button"));
		$content->addHtml($modal->create("Loading content...",true));
		$html = $page->html();
		$html->addHtml($container->createHtml());
	}
	protected function _addGridProperties(\ClinicLE\Client\Container\Container $container, \ClinicLE\Client\Container\Table
    $table) {
		$settings = $this->getSettings();
		if($this->getSetting("use_parent")) {
			$settings['parentid']=ADD::sys()->getParam('eid');
		}
		$settings['pid']=ADD::sys()->getPatientId();
		$container->appendProperties($settings);
	}

	function getClassType() {
		return parent::getClassType()." grid";
	}
	function _instantiateRowActions($actionSetting) {
		$rowActionsSetting = $this->getSetting($actionSetting);
		if(!$rowActionsSetting) {
            $rowActionList = array();
        }
        else {
            $rowActionList = explode(";", $rowActionsSetting);
        }
        if(CLE::sys()->getAccessManager()->isAdmin() && is_a($this,ADD_Field_EncounterGrid::class)) {
            if ($actionSetting == "after_row_actions") {
                if (!in_array("DeleteRecord", $rowActionList)) {
                    $rowActionList[] = "AdminDeleteRecord";
                }
                if (!in_array("EditRecord", $rowActionList)) {
                    $rowActionList[] = "AdminEditRecord";
                }
            }
        }
		$rowActions = array();
		foreach($rowActionList as $rowAction) {
			$rowActions[]=$this->_createRowAction($rowAction);
		}
		return $rowActions;
	}
	
	protected function _createRowAction($rowActionName) {
	    $gridAction = ADD::sys()->newFactoryInstance(ClinicLE\Client\Factory\ClientFactories::GRID_ACTION, $rowActionName);
        
		//$gridAction = ADD_Util::loadClass("ADD_Client_Action_".$rowActionName);
		if($gridAction instanceof ADD_Client_Action_GridFieldContainerInterface) {
			$gridAction->setGridField($this);
		}
		return $gridAction;
	}

	function addRowActions($rowActions,\ClinicLE\Client\Container\Table $table) {
	    foreach($rowActions as $rowAction) {
            $table->addCell(new \ClinicLE\Client\Container\Cell\RowActionCell($rowAction));
        }
    }
    function getGridField($fieldName,$form) {
        return $form->getField($fieldName);
    }
}
