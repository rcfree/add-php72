<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Client\Container\Cell\FormFieldCell;

class ADD_Field_EncounterGrid extends ADD_Field_Grid {
	protected $stored = false;
	private $remoteSite;
	protected $_usePage;
	private $page;
	
	protected function getContainingForm($page) {
		$form = $this->_usePage->getComponent('Form');
		$form->setup();
		return $form;
	}
	function setupInfo($page) {
		$add = ADD::sys();
		$this->page = $page;
		$this->remoteSite = $this->getSetting("remote_site");
		parent::setupInfo($page);
		
		if($this->getSetting('no_title')) $this->setTitleHidden(true);
		if($this->remoteSite) {
			return;
		}
		
		$this->_usePageName = $this->assertSetting('use_page');
		
		$useTemp = explode(":", $this->_usePageName);
		if (count($useTemp) == 1) {
			$useMod = $page->getModule();
			$this->_useModuleName = $useMod->getName();
			$this->_usePage = $useMod->getPage($this->_usePageName);
			$this->setSetting('use_page',$this->_useModuleName.":".$this->_usePageName);
		} else {
			$this->_usePage = ADD::sys()->getPageInModule($useTemp[0], $useTemp[1]);
			$this->_useModuleName = $useTemp[0];
		}
		if(!$this->_usePage) throw new ADD_PageNotFoundException("Use page '".$this->_usePageName."' not found");
	}
	private function _getFieldsInTable($form) {
		$colFieldNameList = $this->getSetting('fields');
		if (empty($colFieldNameList)) return $form->getFields();

		$colFieldNames = $colFieldNameList ? explode(";", $colFieldNameList) : array();
		return $this->_confirmFieldsExist($colFieldNames,$form);
		
	}
	function _confirmFieldsExist($colFieldNames,$form) {
		$colFields = array();

		foreach ($colFieldNames as $colFieldName) {
			$field = $this->getGridField($colFieldName,$form);
			if (!$field){
                $colFields[]=null;
				//throw new ADD_FieldException("Field name '$colFieldName' specified as column in EncounterTable not found in form definition");
            }
			$colFields[] = $field;
		}
		return $colFields;
	}

	function createRemoteHeaders($colFieldNames) {
		if($this->getSetting('col_headings')) {
			return explode(";",$this->getSetting('col_headings'));
		}
		
		return array_map(function($fieldName) { return ADD_Util::nameToLabel($fieldName);}, $colFieldNames);
	}
	function createLocalData($colFields, $form,\ClinicLE\Client\Container\Table $table) {
        if($this->getSetting('col_headings')) {
            $table->setHeadings(explode(";",$this->getSetting('col_headings')));
        }
		foreach ($colFields as $colField) {
		    if(!$colField) {
		        continue;
            }
			$childFields = $form->getChildFields($colField->getName());
            $cell = new FormFieldCell($childFields,$colField);

            $table->addCell($cell);
		}
	}

	function createRemoteRow($colFieldNames) {
		$data = array();
		foreach ($colFieldNames as $colField) {
			$fieldText = "row.".$colField.".label";
			$cell = '<span ng-bind-html="'.$fieldText.'"></span>';
			$data[] = $cell;
		}
		
		return array($data);
	}

	function generateTableData(\ClinicLE\Client\Container\Table $table, $page) {
		$add = ADD::sys();
		
		if($this->remoteSite) {
			$colFieldNames = explode(";",$this->getSetting('fields'));
			$this->createRemoteHeaders($colFieldNames, $table);
			$this->createRemoteRow($colFieldNames, $table);
		}
		else {
			$form = $this->getContainingForm($page);
		
			$colFields = $this->_getFieldsInTable($form);
		
			$this->createLocalData($colFields,$form, $table);
		}

	}

	function getDataUrl() {
		return $this->remoteSite ? "/api/remote-encounters" : "/api/encounters";
	}
	
	function _addGridProperties(\ClinicLE\Client\Container\Container $container, \ClinicLE\Client\Container\Table
    $table) {
        $container->putProperty("pid",ADD::sys()->getPatientId());
		
		$dataUrl = $this->getSetting('data_url'); 
		if($dataUrl) {
			$container->putProperty("dataurl",$dataUrl);
		}
		else {
			$container->putProperty("dataurl",$this->getDataUrl());
		}
		
		if($this->remoteSite) {
			$container->putProperty("site",$this->remoteSite);
		}
		$container->putProperty("pim",$this->assertSetting('use_page'));
			
		$this->addFilters($container);
		$this->addRowConditions($table);
		
		$eid = ADD::sys()->getParam('eid');
		if ($eid && $this->getSetting('use_parent')) {
			$container->putProperty("parentid",$eid);
		}
		if($eid &&  $this->getSetting('parent_type')) {
			$container->putProperty("confirmparenttype",$this->getSetting('parent_type'));
		}
		if($this->getSetting('linkfield')) {
			$container->putProperty("linkfield",$this->getSetting('linkfield'));
		}
		if($this->getSetting('link_copydata')) {
			$container->putProperty("linkcopydata",$this->getSetting('link_copydata'));
		}
		if($this->getSetting('refresh')) {
			$container->putProperty("refresh",$this->getSetting('refresh'));
		}
		if($this->getSetting('linkparent')) {
			$container->putProperty("linkparent",$this->getSetting('linkparent'));
		}
		if($this->getSetting('include_fields')) {
			$container->putProperty("includefields",$this->getSetting('include_fields'));
		}
        if($this->getSetting("fieldmap")) {
            $container->putProperty("fieldmap",implode("|",$this->getSetting('fieldmap')));
        }
		if(!$this->getSetting('sort_field')) return;
		$container->putProperty("sortfield",$this->getSetting('sort_field'));
		$container->putProperty("sortorder",$this->getSetting('sort_order'));
		$container->putProperty("sortdate",$this->getSetting('sort_date'));


	}
	
	function addFilters(\ClinicLE\Client\Container\Container $container) {
		$filters = array();
			
		$filter = $this->getSetting('filter');
		if($filter) {
			foreach($filter as $filterItem) {
				$filters[]=$filterItem;
			}
		}
		
		$filterFields = $this->getSetting('filter_field');
		
		if ($filterFields) {
			$filterValues = $this->getSetting('filter_value');
			$filterOps = $this->getSetting('filter_op','eq');

			$fcounter=1;
			foreach($filterFields as $filterField) {
				$filters[] = implode("|",array($filterField,$filterOps[$fcounter-1],$filterValues[$fcounter-1]));
				$fcounter++;
			}
		}
		$container->putProperty("filter",implode("+",$filters));

        if($this->getSetting('filtergroup')) {
		    $container->putProperty("filtergroup",implode("+",$this->getSetting('filtergroup')));
		}

	}
	
	function addRowConditions($gridHelper) {
		$rowConditions = $this->getSetting('row_condition');
		
		if (!$rowConditions) return;

		$gridHelper->setRowConditions(explode(";",$rowConditions));
	}

	function _createRowAction($rowActionName) {
	    $gridAction = ADD::sys()->newFactoryInstance(ClinicLE\Client\Factory\ClientFactories::GRID_ACTION, $rowActionName,array($this->remoteSite ? $this->page : $this->_usePage));
        
        if($gridAction instanceof ADD_Client_Action_GridFieldContainerInterface) {
			$gridAction->setGridField($this);
		}
		return $gridAction;
	}

    /**
     * @param $childFields
     * @param $colField
     * @return string
     */
    public function generateCellContent($childFields, $colField)
    {
        if (count($childFields) > 0) {
            $subResults = array();
            $joinChar = $colField->getSetting('join_char') ? $colField->getSetting('join_char') : " ";

            foreach ($childFields as $childField) {
                $subResults[] = "{{row." . $childField->getName() . ".label}}";
            }
            $cell = implode($joinChar, $subResults);
            return $cell;
        }
        $fieldText = "row." . $colField->getName() . ".label";
        $cell = '<span ng-bind-html="' . $fieldText .  ' | trustHtml"></span>';
        return $cell;
    }
}
