<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\System\Service\CoreServices;

class ADD_Page_Api_Encounters extends ADD_Page_Api {
	private $filters = array();
	private $_form;
	private $_page;
	
	function isOnlyLabelOutput() {
		return false;
	}
	function createFilter($filterItem, $filterGroup) {
		if(!$filterGroup) {
			return new ADD_Client_Filter($filterItem);
		}	
		
		list($fieldName,$filterOp,$filterValue) = explode("|",$filterItem);
		$form = $this->_page->getComponent('Form');
		$form->setup($this->_page);
		$filterItems = ADD::db('dictionary')->getDictionaryItemsInGroup($form->getField($fieldName)->getSetting('dictionary'),$filterValue);
		$values = array();
		foreach($filterItems as $filterItem) {
			$values[]=$filterItem['value'];
		}
		$filterItem = implode("|",array($fieldName,$filterOp,implode(";",$values)));
		return new ADD_Client_Filter($filterItem);
	}
		
	function apiOutput() {
		$add = ADD::sys();
		$log = ADD::log($this);
        /** @var $repo \ClinicLE\Data\Repository\AbstractEncounterRepository */
		$repo = $add->getService(CoreServices::ENCOUNTER_REPOSITORY);
		$query = $repo->createQuery();

		$pageInModuleName = $add->assertParam('pim');
		$patientId = $add->getParam('pid');
		$parentId = $add->getParam('parentid');
		$fieldOutput = $add->getParam('fieldOutput','label');
		$specialStatus = $add->getParam('specialstatus');
		$confirmParentType = $add->getParam('confirmparenttype');

		$temp = explode(":",$pageInModuleName);
		$pageInModule = $add->getPageInModule($temp[0],$temp[1]);
		$this->_page = $pageInModule;
		
		if(!$pageInModule->getPermission("view")) throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to view ".$pageInModule->getSetting('single')."s");
		
		$filterParam = $add->getParam('filter');
		
		if($filterParam) {
			foreach(explode("+",$filterParam) as $filterItem) {
				$filter = $this->createFilter($filterItem,$add->getParam('filtergroup'));
				$this->filters[]=$filter;
			}
		}
		$badge = $pageInModule->getSetting('badge');
		
		$encType = $pageInModule->getSetting('encounter');

		$this->_form = $pageInModule->getComponent('Form');
		$this->_form->setup($pageInModule);
		       
        $query->filterByType($encType);
		$query->addEncounterFields("*");
		$query->addObservationFieldsFromForm($this->_form);
		

		if($patientId) { 
                    $query->filterByPatientId($patientId);
		}
		
		if($parentId) {
                    $query->filterBySectionId($parentId);
		}

		if($confirmParentType) {
			$parentPage = $add->getPageFromPim($confirmParentType);
			if($parentPage && $parentPage->getSetting('encounter')) {
				$query->filterBySectionType($parentPage->getSetting('encounter'));
			}
		}

		if($specialStatus) {
                $query->filterByPatientField("special_status",$specialStatus);
		}
		else {
                    $query->filterByPatientField("special_status",null,"is");
		}
		if($badge) {
			$query->filterByEncounterField("badge",$badge);
		}
		
		$this->appendFiltersToQuery($query);
		
		$pageInModule->setupComponents();
		$form = $pageInModule->getComponent('Form');
		foreach($this->filters as $filter) {
			$fieldName = $filter->getField();
			$formField = $form->getField($fieldName);
			$formField->finaliseField($pageInModule);
			
			if(!$add->config("show_filter_labels")) continue;
			if(!is_a($formField,"ADD_Field_List")) continue;
			$optionInfo = array();
			foreach($filter->getValues() as $value) {
				$optionInfo[] = $value ."=".$formField->getOptions()->findLabelWithValue($value);
			}
			ADD::web()->addWarning("Filtering '".$fieldName."' on <ul><li>".implode("</li><li>",$optionInfo)."</li></ul>");
		}
        $this->finaliseQuery($query);

		return $this->outputDataFromQuery($query, $pageInModule);
	}

    function finaliseQuery($query) {
        $add = ADD::sys();
        //$sql = $select." where ".implode(" and ",$where);
        $sortBy = $add->getParam('sortfield');
        $cdt = $this->_getFieldSubQuery($sortBy);

        if(!$sortBy) {
            return;
        }
        if($add->getParam('sortdate')) {
            $query->orderBy("STR_TO_DATE($cdt,'%d/%m/%Y') ",$add->getParam('sortorder',''));
        }
        else {
            $query->orderBy($cdt,$add->getParam('sortorder',''));
        }
    }
	function outputDataFromQuery($query, $page) {
        $edb = ADD::db('encounter');

        $data = array();
        $rows = array();

        $rowsPerPage = ADD::sys()->getParam('rows_per_page');
        $pageNo = ADD::sys()->getParam('page_no');

        if($rowsPerPage) {
            $start = ($pageNo * $rowsPerPage) - $rowsPerPage;
            $query->limitRows($start, $rowsPerPage);
        }

        /** @var  $runner \ClinicLE\Data\Query\QueryRunner */
        $runner = CLE::svc(CoreServices::QUERY_RUNNER);

        $encs = $runner->getRows($query);
        $countEncs = $runner->countRows($query);
        $encType = $page->getSetting('encounter');

        $encForm = $edb->getEncounterFormWithType($encType);
        if(!$encForm) {
            throw new \Exception("Encounter form with type:".$encType." was not found");
        }
        $obsFields = $edb->getObservationFieldsInEncounterForm($encForm['id']);

        foreach($encs as $enc) {
            $row = $enc;
            foreach($obsFields as $obsField) {
                $obsName = $obsField['name'];

                $obsValue = ADD_Util::val($obsName,$row);
                $row[$obsName]=$this->isOnlyLabelOutput() ? $this->_getFieldLabel($obsValue,$obsName) : array("value"=>$obsValue,"label"=>$this->_getFieldLabel($obsValue,$obsName));
            }

            $rows[]=$row;
        }
        $data['data']=$rows;
        $data['total']=$countEncs + 0;

        if(!$rowsPerPage) {
            return $data;
        }

        $firstRow = ($pageNo * $rowsPerPage) - $rowsPerPage;

        $data['first']=$firstRow+1;
        $data['last']=$firstRow + count($rows);
        $data['rows_per_page']=$rowsPerPage;
        $data['page_no']=$pageNo;
        if($query->isDisabled()) {
            $data['disabled']=1;
        }
        return $data;
    }
	function _getFieldSubQuery($fieldName) {
		return "(select concat(o.value, coalesce(o.note,'')) from observation o
					left join observation_field of on o.observation_field_id=of.id
					where o.encounter_id=e.id and of.name='$fieldName'
				)";
	}
	
	function appendFiltersToQuery($query) {
		if(!$this->filters) return;
		$edb = ADD::db('encounter');
		
		foreach($this->filters as $filter) {
			$fieldSquery = $this->_getFieldSubQuery($filter->getField());
			if($filter->getValues()==array("null")) {
				$query->addFilter("(".$fieldSquery." is null or $fieldSquery = '')");
			}
			else {
				$pholders = $edb->makePlaceholders($filter->getValues());
				
				$query->filterByObservationField($filter->getField(),$filter->getValues());
			}			
		}
		
	}
	function _getFieldLabel($obsValue,$obsName) {
		$field = $this->_form->getField($obsName);
		if(!$field) {
			ADD::log()->warn("field $obsName not found");
			return null;
		}
		$field->setValue($obsValue);
		
		return $field->getDisplayText($this->_page,null);
	}
}